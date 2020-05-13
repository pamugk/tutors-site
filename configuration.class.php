<?php
include "setup.php";
require('vendor/autoload.php');

class Config {
    private static $instance = null;
    public $config = array();

    private function __construct() {
        $cfg_file = PATH . '/includes/config.inc.php';

        if (file_exists($cfg_file))
            include($cfg_file);
        else
            $_CFG = array();

        $d_cfg = self::getDefaultConfig();
        $this->config = array_merge($d_cfg, $_CFG);

        date_default_timezone_set($this->config['timezone']);

        setlocale(LC_ALL, "ru_RU.UTF-8");

        foreach ($this->config as $id => $value)
            $this->{$id} = $value;

        return true;
    }
    
    public static function getInstance() {
        if (self::$instance === null)
            self::$instance = new self;
        return self::$instance;
    }

    public static function getDefaultConfig() {
        $cfg['timezone'] = 'Asia/Yekaterinburg';
        $cfg['db_host'] = '34.68.106.58';
        $cfg['db_port'] = '5432';
        $cfg['db_base'] = 'tutors';
        $cfg['db_user'] = 'postgres';
        $cfg['db_pass'] = 'postgres';

        return $cfg;
    }

    public static function getConfig($value = '') {
        if ($value && isset(self::getInstance()->{$value}))
            return self::getInstance()->{$value};
        else
            return self::getInstance()->config;
    }

    public static function saveToFile($_CFG, $file = 'config.inc.php') {
        $filepath = PATH . '/includes/' . $file;

        if (file_exists($filepath)) {
            if (!is_writable($filepath)) {
                print("Файл <strong>' . $filepath . '</strong> недоступен для записи!");
                return false;
            }
        }
        elseif(!is_writable(dirname($filepath))) {
            print('Папка <strong>' . dirname($filepath) . '</strong> недоступна для записи!');
            return false;
        }

        $cfg_file = fopen($filepath, 'w+');

        fputs($cfg_file, "<?php \n");
        fputs($cfg_file, '$_CFG = array();' . "\n");

        foreach ($_CFG as $key => $value) {
            if (is_int($value))
                $s = '$_CFG' . "['$key'] \t= $value;\n";
            else
                $s = '$_CFG' . "['$key'] \t= '$value';\n";
            fwrite($cfg_file, $s);
        }

        fwrite($cfg_file, "?>");
        fclose($cfg_file);

        return true;
    }
}
?>