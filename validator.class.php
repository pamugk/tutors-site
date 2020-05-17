<?php
include_once "database.class.php";

class Validator {
    private static $passwordPersonalFields = array(
        'password' => array('name' => 'Пароль', 'type' => 'string'), 
        'password2' => array('name' => 'Повтор пароля', 'type' => 'string')
    );

    private static $requiredLoginFields = array(
        'login' => array('name' => 'Логин', 'type' => 'string'), 
        'password' => array('name' => 'Пароль', 'type' => 'string')
    );

    private static $requiredPersonalFields = array(
        'name' => array('name' => 'Имя', 'type' => 'string'), 
        'surname' => array('name' => 'Фамилия', 'type' => 'string'), 
        'login' => array('name' => 'Логин', 'type' => 'string')
    );

    private static $requiredRegistrationFields = array(
        'name' => array('name' => 'Имя', 'type' => 'string'), 
        'surname' => array('name' => 'Фамилия', 'type' => 'string'),
        'login' => array('name' => 'Логин', 'type' => 'string'),
        'password' => array('name' => 'Пароль', 'type' => 'string'), 
        'password2' => array('name' => 'Повтор пароля', 'type' => 'string')
    );

    private static $requiredTutorFields = array(
        'experience' => array('name' => 'Стаж', 'type' => 'integer', 'options' => array('options' => array('min_range' => 0, 'max_range' => 120))),
        'price' => array('name' => 'Оплата за 1 час', 'type' => 'integer', 'options' => array('options' => array('min_range' => 0, 'max_range' => 9223372036854775807)))
    );

    private static $requiredContactFields = array(
        'email' => array('name' => 'E-mail', 'type' => 'email'),
        'msg' => array('name' => 'Текст сообщения', 'type' => 'string')
    );

    private static function validateFields($info, $fields) {
        foreach ($fields as $key => $value) {
            if (!array_key_exists($key, $info))
                return "Не заполнено поле '".$value['name']."'";
            $outcome = self::validateValueType($info[$key], $value);
            if ($outcome !== false)
                return $outcome;
        }
        return false;
    }

    private static function validateValueType($value, $field) {
        $result = false;
        switch ($field['type']) {
            case  'string': {
                if ($value == '')
                    $result = "Поле '".$field['name']."' не может быть пустым";
                break;
            }
            case 'integer': {
                if (filter_var($value, FILTER_VALIDATE_INT, $field['options']) === false)
                    $result = "Поле '".$field['name']."' должно содержать целое число (в промежутке от ".$field['options']['options']['min_range']." до ".$field['options']['options']['max_range'] .")";
                break;
            }
            case 'email': {
                if (filter_var($value, FILTER_VALIDATE_EMAIL) === false)
                    $result = "Поле '".$field['name']."' должно содержать корректный e-mail";
                break;
            }
        }
        return $result;
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
        return false;
    }

    public static function validatePageData(&$info) {
        if (!isset($info['n']) or $info['n'] == null) {
            $info['n'] = 1;
        } elseif (!is_numeric($info['n'])) {
            return 'Неверно указан номер страницы!';
        } elseif ($info['n'] < 1) {
            return 'Номер страницы - положительное число!';
        }

        if (!isset($info['s']) or $info['s'] == null) {
            $info['s'] = 10;
        } elseif (!is_numeric($info['s'])) {
            return 'Неверно указано количество элементов на странице!';
        } elseif ($info['s'] < 1) {
            return 'Количество элементов на странице - положительное число!';
        }

        if (!isset($info['q']) or $info['q'] == null) {
            $info['q'] = '';
        }

        if (!isset($info['ts']) or $info['ts'] == null) {
            $info['ts'] = null;
        } elseif (!is_numeric($info['ts']) or $info['ts'] < 0) {
            return 'Неверно указан предмет!';
        }

        if (!isset($info['o']) or $info['o'] == null) {
            $info['o'] = 0;
        } elseif (!is_numeric($info['o']) or $info['o'] < 0 or $info['o'] > 4) {
            return 'Неверно указан порядок!';
        }
        return false;
    }

    public static function validateCountData(&$info) {
        if (!isset($info['q']) or $info['q'] == null) {
            $info['q'] = '';
        }

        if (!isset($info['ts']) or $info['ts'] == null) {
            $info['ts'] = null;
        } elseif (!is_numeric($info['ts']) or $info['ts'] < 0) {
            return 'Неверно указан предмет!';
        }

        if (!isset($info['o']) or $info['o'] == null) {
            $info['o'] = 0;
        } elseif (!is_numeric($info['o']) or $info['o'] < 0 or $info['o'] > 4) {
            return 'Неверно указан порядок!';
        }
        return false;
    }

    public static function validateContactInfo($info) {
        $fieldValidation = self::validateFields($info, self::$requiredContactFields);
        if ($fieldValidation)
            return $fieldValidation;
        return false;
    }
}
?>