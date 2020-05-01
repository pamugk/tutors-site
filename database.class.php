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

    public function getPathPage($path) {
        return self::executePreparedQuery("getPathPage", 'SELECT page FROM site.pages WHERE route = $1;', array($path));
    }

    public function loginExists($login) {
        return false;
    }

    public function registerUser($userInfo) {
        self::executePreparedQuery('createUser', 
        'INSERT INTO "data".users(login, hash, "name", surname, patronymic, is_tutor) VALUES($1, $2, $3, $4, $5, $6);', 
        $userInfo);
    }
}
?>