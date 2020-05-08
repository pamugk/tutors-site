<?php
include "configuration.class.php";

class Database {
    private static $instance;
    public $db_link;

    private function __construct() {
        $this->inConf = Config::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null)
            self::$instance = new self;
        return self::$instance;
    }

    private static function initConnection() {
        $inConf = Config::getInstance();
        $db_link = pg_connect("host = $inConf->db_host port = $inConf->db_port dbname = $inConf->db_base user=$inConf->db_user password=$inConf->db_pass");
        if (!$db_link)
            print("No DB connection");
        else
            pg_set_client_encoding($db_link, 'utf8');
        return $db_link;
    }

    public static function reinitializeConnection() {
        if (self::getInstance()->db_link == null || !self::getInstance()->db_link == null)
            self::getInstance()->db_link = self::initConnection();
        return true;
    }

    private static function toPgArray(array $data, $escape = 'pg_escape_string')
    {
        $result = [];

        foreach ($data as $element) {
            if (is_array($element))
                $result[] = static::toPgArray($element, $escape);
            elseif ($element === null)
                $result[] = 'NULL';
            elseif ($element === true)
                $result[] = 'TRUE';
            elseif ($element === false)
                $result[] = 'FALSE';
            elseif (is_numeric($element))
                $result[] =  "$element";
            elseif (is_string($element))
                $result[] = "'$escape($element)'";
            else
                throw new \InvalidArgumentException("Unsupported array item");
        }

        return sprintf('ARRAY[%s]', implode(',', $result));
    }

    public function checkSubjectsTaughtByTutor($tutorId) {
        $result = $this::executePreparedQuery('checkSubjectsTaughtByTutor',
        'WITH tutorSubjects AS (SELECT teaching_subject_id FROM "data".ref_users_teaching_subjects WHERE user_id=$1) 
        SELECT id, "name", tutorSubjects.teaching_subject_id is not null
        FROM "data".teaching_subjects LEFT JOIN tutorSubjects ON id = tutorSubjects.teaching_subject_id;',
        array($tutorId));
        $subjects = array();
        if ($result) {
            while($row = pg_fetch_array($result))
                array_push($subjects, array('id' => $row[0], 'name' => $row[1], 'isTaughtBy' => $row[2] == 't'));
            $this->freeResult($result);
        }
        return $subjects;
    }

    private function collectSubjects($result) {
        $subjects = array();
        if ($result) {
            while ($row = pg_fetch_array($result))
                $subjects[$row[0]] = $row[1];
            $this->freeResult($result);
        }
        return $subjects;
    }

    private function executePreparedQuery($name, $query, $params) {
        $db_link = $this->db_link;
        pg_prepare($db_link, $name, $query);
        return pg_execute($db_link, $name, $params);
    }

    private function executeQuery($query) {
        return pg_query($this->db_link, $query);
    }

    public function checkLoginNotAccessible($userId, $newLogin) {
        $result = $this->executePreparedQuery("loginExists", 
        'SELECT EXISTS (SELECT * FROM "data".users WHERE id != $1 AND login=$2);', 
        array($userId, $newLogin));
        $exists = pg_fetch_array($result)[0] == 't';
        $this->freeResult($result);
        return $exists;
    }

    public function clearTutorSubjects($tutorId) {
        $this->executePreparedQuery('clearTutorSubjects', 'DELETE FROM "data".ref_users_teaching_subjects WHERE user_id=$1;',
        array($tutorId));
    }

    public function freeResult($result) {
        pg_free_result($result);
    }

    public function getCountTutors() {
        $result = $this::executeQuery('SELECT count(*) FROM data.users WHERE is_tutor=True;');
        $countTutors = null;
        if ($result) {
            $countTutors = pg_fetch_array($result)[0];
            $this->freeResult($result);
        }
        return $countTutors;
    }

    public function getCredentials($login) {
        return $this->executePreparedQuery("getCredentials", 'SELECT id, hash FROM "data".users WHERE login=$1', array($login));
    }

    public function getListTutors($pageNum, $pageSize) {
        $result = $this::executePreparedQuery("getListTutors", '
                SELECT name, surname, patronymic, experience 
                FROM data.users 
                WHERE is_tutor=True
                LIMIT $1 
                OFFSET $2;',
            array($pageSize, ($pageNum-1)*$pageSize)
        );

        $tutors = array();
        if ($result) {
            $tutor = pg_fetch_array($result);
            while ($tutor) {
                array_push($tutors, array('name' => $tutor[0], 'surname' => $tutor[1], 'patronymic' => $tutor[2], 'experience' => $tutor[3]));
                $tutor = pg_fetch_array($result);
            }

            $this->freeResult($result);
        }

        return $tutors;
    }

    public function getPathPage($path) {
        return $this->executePreparedQuery("getPathPage", 'SELECT page FROM site.pages WHERE route = $1;', array($path));
    }

    public function getSubjects() {
        return $this->collectSubjects($this::executeQuery('SELECT * from "data".teaching_subjects'));
    }

    public function getSubjectsOfTutor($tutorId) {
        return $this->collectSubjects($this::executePreparedQuery('getSubjectsOfTutor', 
        'WITH tutorSubjects AS (SELECT teaching_subject_id FROM "data".ref_users_teaching_subjects WHERE user_id=$1) 
        SELECT id, "name" FROM teaching_subjects, tutorSubjects WHERE id = tutorSubjects.teaching_subject_id;',
        array($tutorId)));
    }

    public function getTutorInfo($tutorId) {
        $result = $this->executePreparedQuery('getTutorInfo', 'SELECT about, experience FROM "data".tutors WHERE id=$1', array($tutorId));
        if ($result) {
            $row = pg_fetch_array($result);
            $info = array('about' => $row[0], 'experience' => $row[1]);
            $this->freeResult($result);
        }
        else $info = array();
        return $info;
    }

    public function getUser($userId) {
        $result = $this->executePreparedQuery("getUserBySession", 
        'SELECT login, name, surname, patronymic, is_tutor, avatar_id FROM "data".users WHERE id = $1;', array($userId));
        $user = null;
        if ($result) {
            $row = pg_fetch_array($result);
            $user = array("login" => $row[0], "name" => $row[1], "surname" => $row[2], "patronymic" => $row[3], "isTutor" => $row[4] == 't',
            "avatarId" => pg_field_is_null($result, 0, 5) == 1 ? null : $row[5]);
            $this->freeResult($result);
        }
        return $user;
    }

    public function getImage($imageId) {
        $result = $this->executePreparedQuery("getImage", 'SELECT content FROM "data".images WHERE id=$1', array($imageId));
        $image = false;
        if ($result) {
            $image = pg_unescape_bytea(pg_fetch_result($result, 'content'));
            $this::freeResult($result);
        }
        return $image;
    }

    public function isTutor($userId) {
        $result = $this->executePreparedQuery("isTutor", 'SELECT is_tutor FROM "data".users WHERE id=$1;', array($userId));
        $isTutor = pg_fetch_array($result)[0] == 't';
        return $isTutor;
    }

    public function loginExists($login) {
        $result = $this->executePreparedQuery("loginExists", 'SELECT EXISTS (SELECT * FROM "data".users WHERE login=$1);', array($login));
        $exists = pg_fetch_array($result)[0] == 't';
        $this::freeResult($result);
        return $exists;
    }

    public function registerUser($userInfo) {
        $this->executePreparedQuery('createUser',
        'INSERT INTO "data".users(login, hash, "name", surname, patronymic, is_tutor) VALUES($1, $2, $3, $4, $5, $6);', 
        $userInfo);
    }

    public function saveImage($image) {
        $safeImage = pg_escape_bytea($this->db_link, $image);
        $result = $this->executePreparedQuery('saveImage', 
        'INSERT INTO "data".images(content) VALUES($1) RETURNING id;', array($safeImage));
        $id = pg_fetch_result($result, 0, 0);
        $this->freeResult($result);
        return $id;
    }

    public function setUserAvatar($userId, $imageId) {
        $this->executePreparedQuery('setUserAvatar', 'UPDATE "data".users SET avatar_id=$2 WHERE id=$1;',
        array($userId, $imageId));
    }

    public function updateTutorInfo($tutorInfo) {
        $this->executePreparedQuery('updateTutorInfo', 
        'UPDATE "data".tutors SET about=$2, experience=$3  WHERE id=$1;', $tutorInfo);
    }

    public function updateTutorSubjects($tutorId, $subjectsIds) {
        $subjects = self::toPgArray($subjectsIds);
        $this->executePreparedQuery('updateTutorSubjects', 
        "INSERT INTO data.ref_users_teaching_subjects (user_id, teaching_subject_id) 
        SELECT $1 usr_id, subj_id FROM unnest($subjects) subj_id;",
        array($tutorId));
    }

    public function updateUserInfo($userInfo) {
        $this->executePreparedQuery('updateUserInfo', 
        'UPDATE "data".users SET "login"=$2, "name"=$3, surname=$4, patronymic=$5, is_tutor=$6 WHERE id=$1;', $userInfo);
    }

    public function updateUserInfoWPassword($userInfoWPassword) {
        $this->executePreparedQuery('updateUserInfoWPassword', 
        'UPDATE "data".users SET "login"=$2, hash=$3 "name"=$4, surname=$5, patronymic=$6, is_tutor=$7 WHERE id=$1;', 
        $userInfoWPassword);
    }
}
?>