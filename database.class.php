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

    public function getUserBySession($sessionId) {
        return $this::executePreparedQuery("getUserBySession", 
        'WITH usr AS (SELECT user_id FROM "data".sessions WHERE id = $1 AND is_active AND expiration < NOW()) 
        SELECT id, login, "name", surname, patronymic, is_tutor 
        FROM "data".users, usr WHERE usr.user_id = id;', array($sessionId));
    }

    public function loginExists($login) {
        $result = $this::executePreparedQuery("loginExists", 'SELECT EXISTS (SELECT * FROM "data".users WHERE login=$1);', array($login));
        $exists = pg_fetch_array($result)[0] == 'true';
        $this::freeResult($result);
        return $exists;
    }

    public function startSession($userId) {
        return $this::executePreparedQuery('startSession', 
        'INSERT INTO "data".sessions(user_id) VALUES($1) RETURNING id;', array($userId));
    }

    public function stopSession($sessionId) {
        $this::executePreparedQuery('stopSession', 'UPDATE "data".sessions SET is_active=false WHERE id=$1;', array($sessionId));
    }

    public function registerUser($userInfo) {
        $this::executePreparedQuery('createUser',
        'INSERT INTO "data".users(login, hash, "name", surname, patronymic, is_tutor) VALUES($1, $2, $3, $4, $5, $6);', 
        $userInfo);
    }
}
?>