<?php
include_once "database.class.php";
class PgSessionHandler implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface {
    private static $instance;
    
    public static function getInstance() {
        if (self::$instance === null)
            self::$instance = new self;
        return self::$instance;
    }

    public function close() {
        return true;
    }

    public function create_sid() {
        Database::reinitializeConnection();
        return Database::getInstance()->sessionCreate();
    }

    public function destroy($session_id) {
        Database::reinitializeConnection();
        return Database::getInstance()->sessionDestroy($session_id);
    }

    public function gc($maxlifetime) {
        Database::reinitializeConnection();
        return Database::getInstance()->sessionGC($maxlifetime);
    }

    public function open($save_path, $session_name) {
        return true;
    }

    public function read($session_id) {
        Database::reinitializeConnection();
        return Database::getInstance()->sessionRead($session_id);
    }

    public function write($session_id, $session_data) {
        Database::reinitializeConnection();
        return Database::getInstance()->sessionWrite($session_id, $session_data);
    }

    public function validateId($session_id){
        Database::reinitializeConnection();
        return Database::getInstance()->sessionValidateId($session_id);
    }

    public function updateTimestamp($session_id, $sessionData){
        Database::reinitializeConnection();
        return Database::getInstance()->sessionUpdateTimestamp($session_id);
    }
}
?>