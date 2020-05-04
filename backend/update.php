<?php
include_once "../database.class.php";
include_once "../validator.class.php";
$response_code = 418;
$response = "";
session_start();
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $response_code = 405;
    header("Allow: POST");
    $response = array('error' => "Запрос не поддерживается");
}
elseif(key_exists('ID',$_SESSION)) {
    if ($validationResult = Validator::validatePersonalInfo($_POST)) {
        $response_code = 400;
        $response = array('error' => $validationResult);
    }
    else {
        $response_code = 200;
        Database::reinitializeConnection();
        if (key_exists('changePassword', $_POST))
            Database::getInstance()->updateUserInfoWPassword(
                array($_SESSION['ID'], $_POST['login'], password_hash($_POST['password'], PASSWORD_BCRYPT),
                $_POST['name'], $_POST['surname'], $_POST['patronymic'], key_exists('isTutor', $_POST)));
        else
            Database::getInstance()->updateUserInfo(
                array($_SESSION['ID'], $_POST['login'], $_POST['name'], $_POST['surname'], 
                $_POST['patronymic'], key_exists('isTutor', $_POST)));
    }
}
else {
    $response_code = 401;
    $response = array('error' => "Не осуществлён вход");
}
http_response_code($response_code);
echo json_encode($response)
?>