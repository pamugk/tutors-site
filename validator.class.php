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

    private static $requiredTutorFields = array(
        'experience' => 'Стаж'
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

    public static function validatePersonalInfo($info, $files, $id) {
        $fieldValidation = self::validateFields($info, self::$requiredPersonalFields);
        if ($fieldValidation)
            return $fieldValidation;
        Database::reinitializeConnection();
        if (Database::getInstance()->checkLoginNotAccessible($id, $info['login']))
            return "Логин уже занят";
        if (array_key_exists('changePassword', $info)) {
            $passValidation = self::validateFields($info, self::$passwordPersonalFields);
            if ($passValidation)
                return $passValidation;
            if (strcmp($info['password'], $info['password2']) != 0)
                return 'Не совпадают пароль и его повтор';
        }
        if (key_exists('avatar', $files) && $files["avatar"]["tmp_name"] != '') {
            if(!getimagesize($files["avatar"]["tmp_name"]))
                return "Загружаемый файл - не изображение";
            elseif ($files["avatar"]["size"] > 5242880)
                return "Загружаемое изображение должно иметь вес не более 5 Мб";
            else {
                $imageFileType = strtolower(pathinfo(basename($_FILES["avatar"]["name"]),PATHINFO_EXTENSION));
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg")
                  return "Принимаются только JPG, PNG и JPEG изображения";
            }
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

    public static function validateTutorInfo($info) {
        $fieldValidation = self::validateFields($info, self::$requiredTutorFields);
        if ($fieldValidation)
            return $fieldValidation;
        if ($info['experience'] < 0 || $info['experience'] > 120)
            return 'Стаж измеряется в годах, поэтому это - неотрицательное число, имеющее разумное значение (т.е. не больше 120)';
        return false;
    }
}
?>