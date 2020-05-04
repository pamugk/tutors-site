<?php
include_once "database.class.php";

class Validator {
    private static $passwordPersonalFields = array(
        'password' => 'Пароль', 'password2' => 'Повтор пароля'
    );

    private static $requiredLoginFields = array(
        'login' => 'Логин', 'password' => 'Пароль'
    );

    private static $requiredPersonalFields = array(
        'name' => 'Имя', 'surname' => 'Фамилия', 'login' => 'Логин'
    );

    private static $requiredRegistrationFields = array(
        'name' => 'Имя', 'surname' => 'Фамилия',
        'login' => 'Логин', 'password' => 'Пароль', 'password2' => 'Повтор пароля'
    );

    private static function validateFields($info, $fields) {
        foreach ($fields as $key => $value)
            if (!array_key_exists($key, $info) || $info[$key] === '')
                return "Не заполнено поле '$value'";
        return false;
    }

    public static function validateLoginInfo($info) {
        return self::validateFields($info, self::$requiredLoginFields);
    }

    public static function validatePersonalInfo($info) {
        $fieldValidation = self::validateFields($info, self::$requiredPersonalFields);
        if ($fieldValidation)
            return $fieldValidation;
        if (array_key_exists('changePassword', $info)) {
            $passValidation = self::validateFields($info, self::$passwordPersonalFields);
            if ($passValidation)
                return $passValidation;
            if (strcmp($info['password'], $info['password2']) != 0)
                return 'Не совпадают пароль и его повтор';
        }
        return false;
    }

    public static function validateRegistrationInfo($info) {
        $fieldValidation = self::validateFields($info, self::$requiredRegistrationFields);
        if ($fieldValidation)
            return $fieldValidation;
        Database::reinitializeConnection();
        if (Database::getInstance()->loginExists($info['login']))
            return 'Выбранный вами логин уже занят';
        if (strcmp($info['password'], $info['password2']) === 0)
            return false;
        return 'Не совпадают пароль и его повтор';
    }
}
?>