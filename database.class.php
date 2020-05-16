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
        $result = $this::executePreparedQuery('',
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
        $result = $this->executePreparedQuery("",
        'SELECT EXISTS (SELECT * FROM "data".users WHERE id != $1 AND login=$2);',
        array($userId, $newLogin));
        $exists = pg_fetch_array($result)[0] == 't';
        $this->freeResult($result);
        return $exists;
    }

    public function clearTutorSubjects($tutorId) {
        $this->executePreparedQuery('', 'DELETE FROM "data".ref_users_teaching_subjects WHERE user_id=$1;',
        array($tutorId));
    }

    public function freeResult($result) {
        pg_free_result($result);
    }

    public function getCountTutors($search, $teachingSubject) {
        $search = "%{$search}%";
        $and = ($teachingSubject == null or $teachingSubject == 0) ? '' : 'AND u.id in (SELECT user_id FROM data.ref_users_teaching_subjects WHERE teaching_subject_id = $2)';

        $result = $this::executePreparedQuery('', "SELECT count(*) 
                FROM data.users u
                WHERE is_tutor=True
                    AND CONCAT(u.name, ' ', u.surname, ' ', u.patronymic) LIKE $1
                    $and;",
            $and == '' ?  array($search) : array($search, $teachingSubject));
        $countTutors = null;
        if ($result) {
            $countTutors = pg_fetch_array($result)[0];
            $this->freeResult($result);
        }
        return $countTutors;
    }

    public function getCredentials($login) {
        return $this->executePreparedQuery("", 'SELECT id, hash FROM "data".users WHERE login=$1;', array($login));
    }

    public function getListTutors($pageNum, $pageSize, $search, $teachingSubject, $order) {
        $search = "%{$search}%";
        $and = ($teachingSubject == null or $teachingSubject == 0) ? '' : 'AND u.id in (SELECT user_id FROM data.ref_users_teaching_subjects WHERE teaching_subject_id = $4)';
        $orderBy = 'ORDER BY u.id';
        switch ($order) {
            case 0: {           // По умолчанию
                $orderBy = 'ORDER BY u.id';
                break;
            }
            case 1: {           // Сначала дешевле
                $orderBy = 'ORDER BY price';
                break;
            }
            case 2: {           // Сначала дороже
                $orderBy = 'ORDER BY price DESC';
                break;
            }
            case 3: {           // Сначала опытные
                $orderBy = 'ORDER BY experience DESC';
                break;
            }
            case 4: {           // Сначала новички
                $orderBy = 'ORDER BY experience';
                break;
            }
        }
        $result = $this::executePreparedQuery("", "
                SELECT u.id, u.name, surname, patronymic, experience, price, about, avatar_id, array_to_json(array_agg(ts.id)), array_to_json(array_agg(ts.name))
                FROM data.users u
                    JOIN data.tutors t ON u.id = t.id
                    LEFT JOIN data.ref_users_teaching_subjects ruts ON u.id = ruts.user_id
                    LEFT JOIN data.teaching_subjects ts ON ruts.teaching_subject_id = ts.id
                WHERE is_tutor=True
                    AND CONCAT(u.name, ' ', surname, ' ', patronymic) LIKE $1
                    $and
                GROUP BY u.id, surname, patronymic, experience, price, about, avatar_id
                $orderBy
                LIMIT $2
                OFFSET $3;",
            $and == '' ?  array($search, $pageSize, ($pageNum-1)*$pageSize) : array($search, $pageSize, ($pageNum-1)*$pageSize, $teachingSubject)
        );

        $tutors = array();
        if ($result) {
            $tutor = pg_fetch_array($result);
            while ($tutor) {
                array_push($tutors, array('id' => $tutor[0], 'name' => htmlentities($tutor[1]), 'surname' => htmlentities($tutor[2]), 'patronymic' => htmlentities($tutor[3]),
                    'experience' => $tutor[4], 'price' => $tutor[5], 'about' => htmlentities($tutor[6]), 'image' => $tutor[7] == null ? "/images/avatar.png" : "/backend/images.php?id=".$tutor[7], 'ts' => json_decode($tutor[9])));
                $tutor = pg_fetch_array($result);
            }
            $this->freeResult($result);
        }

        return $tutors;
    }

    public function getPathPage($path) {
        return $this->executePreparedQuery("", 'SELECT page FROM site.pages WHERE route = $1;', array($path));
    }

    public function getSubjects() {
        return $this->collectSubjects($this::executeQuery('SELECT * from "data".teaching_subjects'));
    }

    public function getSubjectsOfTutor($tutorId) {
        return $this->collectSubjects($this::executePreparedQuery('',
        'WITH tutorSubjects AS (SELECT teaching_subject_id FROM "data".ref_users_teaching_subjects WHERE user_id=$1) 
        SELECT id, "name" FROM teaching_subjects, tutorSubjects WHERE id = tutorSubjects.teaching_subject_id;',
        array($tutorId)));
    }

    public function getFullTutorInfo($tutorId) {
        $result = $this->executePreparedQuery('', 
            'WITH usr AS (SELECT * FROM "data".users WHERE id=$1) 
            SELECT usr.id, login, usr.name, surname, patronymic, avatar_id, experience, about, price, array_to_json(array_agg(ts.name))
            FROM usr
                JOIN "data".tutors ON usr.id = tutors.id 
                LEFT JOIN "data".ref_users_teaching_subjects ON usr.id = user_id 
                LEFT JOIN "data".teaching_subjects ts ON teaching_subject_id = ts.id
            GROUP BY usr.id, login, usr.name, surname, patronymic, avatar_id, experience, about, price;', array($tutorId));
        $tutorInfo = array('tutor' => null, 'subjects' => array());
        if ($result) {
            $row = pg_fetch_array($result);
            $tutorInfo['tutor'] = array(
                'id' => $row[0], 'login' => $row[1], 'name' => $row[2], 'surname' => $row[3], 
                'patronymic' => pg_field_is_null($result, 0, 4) == 1 ? null : $row[4],
                'avatarId' =>  pg_field_is_null($result, 0, 5) == 1 ? null : $row[5],
                'experience' => $row[6], 'about' => $row[7], 'price' => $row[8]);
            if (pg_field_is_null($result, 0, 9) == 0)
                $tutorInfo['subjects'] = json_decode($row[9]);
            $this->freeResult($result);
        }
        return $tutorInfo;
    }

    public function getTutorInfo($tutorId) {
        $result = $this->executePreparedQuery('', 'SELECT about, experience, price FROM "data".tutors WHERE id=$1', array($tutorId));
        if ($result) {
            $row = pg_fetch_array($result);
            $info = array('about' => $row[0], 'experience' => $row[1], 'price' => $row[2]);
            $this->freeResult($result);
        }
        else $info = array();
        return $info;
    }

    public function getUser($userId) {
        $result = $this->executePreparedQuery("",
        'SELECT id, login, name, surname, patronymic, is_tutor, avatar_id FROM "data".users WHERE id = $1;', array($userId));
        $user = null;
        if ($result) {
            $row = pg_fetch_array($result);
            $user = array(
                "id" => $row[0], "login" => $row[1], "name" => $row[2], "surname" => $row[3], 
                "patronymic" => pg_field_is_null($result, 0, 4) == 1 ? null : $row[4], "isTutor" => $row[5] == 't',
                "avatarId" => pg_field_is_null($result, 0, 6) == 1 ? null : $row[6]);
            $this->freeResult($result);
        }
        return $user;
    }

    public function getImage($imageId) {
        $result = $this->executePreparedQuery("", 'SELECT content FROM "data".images WHERE id=$1', array($imageId));
        $image = false;
        if ($result) {
            $image = pg_unescape_bytea(pg_fetch_result($result, 'content'));
            $this->freeResult($result);
        }
        return $image;
    }

    public function isTutor($userId) {
        $result = $this->executePreparedQuery("", 'SELECT is_tutor FROM "data".users WHERE id=$1;', array($userId));
        $isTutor = pg_fetch_array($result)[0] == 't';
        return $isTutor;
    }

    public function loginExists($login) {
        $result = $this->executePreparedQuery("", 'SELECT EXISTS (SELECT * FROM "data".users WHERE login=$1);', array($login));
        $exists = pg_fetch_array($result)[0] == 't';
        $this->freeResult($result);
        return $exists;
    }

    public function fetchContacts($usrId) {
        $result = $this->executePreparedQuery("", 
            'WITH contacts AS (SELECT "from", "to" FROM "data".messages WHERE "from" = $1 OR "to" = $1) 
            SELECT id, login, name, surname, patronymic, avatar_id 
            FROM "data".users, contacts 
            WHERE id !=$1 AND (id = "from" OR id = "from");', 
            array($usrId));
        $contacts = false;
        if ($result !== FALSE) {
            $contacts = array();
            while ($row = pg_fetch_array($result))
                array_push($contacts, array('id' => $row[0], 'login' => $row[1], 'name' => $row[2], 'surname' => $row[3], 
                    'patronymic' => $row[4], 'avatarId' => $row[5]));
            $this->freeResult($result);
        }
        return $contacts;
    }

    public function messagesFetch($from, $to) {
        $result = $this->executePreparedQuery("", 
            'SELECT id, "from", "to", text FROM "data".messages WHERE "from" = $1 AND "to" = $2 OR "from" = $2 AND "to" = $1;', 
            array($from, $to));
        $messages = false;
        if ($result !== FALSE) {
            $messages = array();
            while ($row = pg_fetch_array($result))
                array_push($messages, array('id' => $row[0], 'from' => $row[1], 'to' => $row[2], 'text' => $row[3]));
            $this->freeResult($result);
        }
        return $messages;
    }

    public function messageSend($from, $to, $message) {
        $result = $this->executePreparedQuery("", 
            'INSERT INTO "data".messages("from", "to", "text") VALUES($1,$2,$3) RETURNING id;', 
            array($from, $to, $message));
        $id = $result ? pg_fetch_result($result, 0, 0) : false;
        $this->freeResult($result);
        return $id;
    }

    public function registerUser($userInfo) {
        $this->executePreparedQuery('',
        'INSERT INTO "data".users(login, hash, "name", surname, patronymic, is_tutor) VALUES($1, $2, $3, $4, $5, $6);',
        $userInfo);
    }

    public function saveImage($image) {
        $safeImage = pg_escape_bytea($this->db_link, $image);
        $result = $this->executePreparedQuery('', 'INSERT INTO "data".images(content) VALUES($1) RETURNING id;', array($safeImage));
        $id = pg_fetch_result($result, 0, 0);
        $this->freeResult($result);
        return $id;
    }

    public function sessionCreate():string {
        $result = $this->executeQuery("INSERT INTO data.sessions(data) VALUES('') RETURNING id;");
        $id = pg_fetch_result($result, 0, 0);
        $this->freeResult($result);
        return $id;
    }

    public function sessionDestroy(string $session_id):bool {
        $this->executePreparedQuery('', 'DELETE FROM data.sessions WHERE id=$1;', array($session_id));
        return true;
    }

    public function sessionGC(int $maxLifetime):bool {
        $this->executePreparedQuery('', 
        'DELETE FROM data.sessions WHERE statement_timestamp()-last_update > make_interval(secs => $1);', array($maxLifetime));
        return true;
    }

    public function sessionRead(string $session_id):string {
        $result = $this->executePreparedQuery('', 'SELECT data FROM data.sessions WHERE id=$1;', array($session_id));
        $data = '';
        if ($result) {
            $data = pg_fetch_result($result, 'data');
            $this::freeResult($result);
        }
        return $data;
    }

    public function sessionWrite(string $session_id, string $session_data):bool {
        $this->executePreparedQuery('', 'UPDATE data.sessions SET last_update=NOW(), data=$2 WHERE id=$1;', array($session_id, $session_data));
        return true;
    }

    public function sessionValidateId(string $session_id):bool {
        $result = $this->executePreparedQuery('', 'SELECT EXISTS(SELECT * FROM data.sessions WHERE id=$1);', array($session_id));
        $valid = pg_fetch_result($result, 0, 0) == 't';
        $this->freeResult($result);
        return $valid;
    }

    public function sessionUpdateTimestamp(string $session_id):bool {
        $result = $this->executePreparedQuery('', 'UPDATE data.sessions SET last_update=NOW() WHERE id=$1;', array($session_id));
        return true;
    }

    public function setUserAvatar($userId, $imageId) {
        $this->executePreparedQuery('', 'UPDATE "data".users SET avatar_id=$2 WHERE id=$1;',
        array($userId, $imageId));
    }

    public function updateTutorInfo($tutorInfo) {
        $this->executePreparedQuery('',
        'UPDATE "data".tutors SET about=$2, experience=$3, price=$4  WHERE id=$1;', $tutorInfo);
    }

    public function updateTutorSubjects($tutorId, $subjectsIds) {
        $subjects = self::toPgArray($subjectsIds);
        $this->executePreparedQuery('',
        "INSERT INTO data.ref_users_teaching_subjects (user_id, teaching_subject_id) 
        SELECT $1 usr_id, subj_id FROM unnest($subjects) subj_id;",
        array($tutorId));
    }

    public function updateUserInfo($userInfo) {
        $this->executePreparedQuery('',
        'UPDATE "data".users SET "login"=$2, "name"=$3, surname=$4, patronymic=$5, is_tutor=$6 WHERE id=$1;', $userInfo);
    }

    public function updateUserInfoWPassword($userInfoWPassword) {
        $this->executePreparedQuery('',
        'UPDATE "data".users SET "login"=$2, hash=$3 "name"=$4, surname=$5, patronymic=$6, is_tutor=$7 WHERE id=$1;',
        $userInfoWPassword);
    }

    public function getAllTeachingSubjects() {
        $result = $this::executeQuery('SELECT id, name FROM data.teaching_subjects');
        $teachingSubjects = null;

        if ($result) {
            $teachingSubjects = pg_fetch_all($result);
            $this->freeResult($result);
        }

        return $teachingSubjects;
    }

    public function contactUs($email, $msg) {
        $result = $this->executePreparedQuery("",
            'INSERT INTO "data".contact_messages("email", "text") VALUES($1, $2) RETURNING id;',
            array($email, $msg));
        $id = $result ? pg_fetch_result($result, 0, 0) : false;
        $this->freeResult($result);
        return $id;
    }
}
?>