<?php
include_once "database.class.php";

class Validator {
    private static $requiredLoginFields = array(
        'login' => 'Логин', 'password' => 'Пароль'
    );

    private static $requiredRegistrationFields = array(
        'name' => 'Имя', 'surname' => 'Фамилия',
        'login' => 'Логин', 'password' => 'Пароль', 'password2' => 'Повтор пароля'
    );

    public static function validateLoginInfo($info) {
        foreach (self::$requiredLoginFields as $key => $value)
            if (!array_key_exists($key, $info) || $info[$key] === '')
                return "Не заполнено поле '$value'";
    }

    public static function validateRegistrationInfo($info) {
        foreach (self::$requiredRegistrationFields as $key => $value)
            if (!array_key_exists($key, $info) || $info[$key] === '')
                return "Не заполнено поле '$value'";
        Database::reinitializeConnection();
        if (Database::getInstance()->loginExists($info['login']))
            return 'Выбранный вами логин уже занят';
        if (strcmp($info['password'], $info['password2']) === 0)
            return false;
        return 'Не совпадают пароль и его повтор';
    }
}
?>