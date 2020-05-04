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

    private function executePreparedQuery($name, $query, $params) {
        $db_link = $this->db_link;
        pg_prepare($db_link, $name, $query);
        return pg_execute($db_link, $name, $params);
    }

    public function freeResult($result) {
        pg_free_result($result);
    }

    public function getCredentials($login) {
        return $this::executePreparedQuery("getCredentials", 'SELECT id, hash FROM "data".users WHERE login=$1', array($login));
    }

    public function getPathPage($path) {
        return $this::executePreparedQuery("getPathPage", 'SELECT page FROM site.pages WHERE route = $1;', array($path));
    }

    public function getUser($userId) {
        $result = $this::executePreparedQuery("getUserBySession", 
        'SELECT login, name, surname, patronymic, is_tutor FROM "data".users WHERE id = $1;', array($userId));
        $user = null;
        if ($result) {
            $row = pg_fetch_array($result);
            $user = array("login" => $row[0], "name" => $row[1], "surname" => $row[2], "patronymic" => $row[3], "isTutor" => $row[4]);
            Database::getInstance()->freeResult($result);
        }
        return $user;
    }

    public function loginExists($login) {
        $result = $this::executePreparedQuery("loginExists", 'SELECT EXISTS (SELECT * FROM "data".users WHERE login=$1);', array($login));
        $exists = pg_fetch_array($result)[0] == 'true';
        $this::freeResult($result);
        return $exists;
    }

    public function registerUser($userInfo) {
        $this::executePreparedQuery('createUser',
        'INSERT INTO "data".users(login, hash, "name", surname, patronymic, is_tutor) VALUES($1, $2, $3, $4, $5, $6);', 
        $userInfo);
    }

    public function updateUserInfo($userInfo) {
        $this::executePreparedQuery('updateUserInfo', 
        'UPDATE "data".users SET login=$2, "name"=$3, surname=$4, patronymic=$5, is_tutor=$6 WHERE id=$1;', $userInfo);
    }

    public function updateUserInfoWPassword($userInfoWPassword) {
        $this::executePreparedQuery('updateUserInfo', 
        'UPDATE "data".users SET login=$2, hash=$3 "name"=$4, surname=$5, patronymic=$6, is_tutor=$7 WHERE id=$1;', 
        $userInfoWPassword);
    }
}
?>