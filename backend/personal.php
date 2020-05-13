<?php
include_once "../database.class.php";
include_once "../validator.class.php";
$response_code = 418;
$response = "";

session_start();
$id = key_exists('ID',$_SESSION) ? $_SESSION['ID'] : false;
session_write_close();
$loggedIn = $id !== false;

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $response_code = 405;
    header("Allow: POST");
    $response = array('error' => "Запрос не поддерживается");
}
elseif($loggedIn) {
    session_write_close();
    if ($validationResult = Validator::validatePersonalInfo($_POST, $_FILES, $id)) {
        $response_code = 400;
        $response = array('error' => $validationResult);
    }
    else {
        $response_code = 200;
        Database::reinitializeConnection();
        if (key_exists('changePassword', $_POST))
            Database::getInstance()->updateUserInfoWPassword(
                array($id, $_POST['login'], password_hash($_POST['password'], PASSWORD_BCRYPT),
                $_POST['name'], $_POST['surname'], $_POST['patronymic'], key_exists('isTutor', $_POST) ? 'true' : 'false'));
        else
            Database::getInstance()->updateUserInfo(
                array($id, $_POST['login'], $_POST['name'], $_POST['surname'], 
                $_POST['patronymic'], key_exists('isTutor', $_POST) ? 'true' : 'false'));
        if (key_exists('avatar', $_FILES) && $_FILES["avatar"]["tmp_name"] != '') {
            $image = file_get_contents($_FILES["avatar"]["tmp_name"]);
            $imageId = Database::getInstance()->saveImage($image);
            Database::getInstance()->setUserAvatar($id, $imageId);
        }
    }
}
else {
    $response_code = 401;
    $response = array('error' => "Не осуществлён вход");
}
http_response_code($response_code);
echo json_encode($response)
?>