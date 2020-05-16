<?php
include_once "../database.class.php";
include_once "../validator.class.php";

include_once "../pgSessionHandler.class.php";
session_set_save_handler(PgSessionHandler::getInstance(), true);

session_start();
$loggedIn = key_exists('ID',$_SESSION);

$response = '';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $response_code = 405;
    header("Allow: POST");
    $response = array('error' => "Запрос не поддерживается");
}
elseif ($loggedIn) {
    $response_code = 418;
    $response = array('error' => "Вход уже был осуществлён");
}
elseif ($validationResult = Validator::validateLoginInfo($_POST)) {
    $response_code = 400;
    $response = array('error' => $validationResult);
}
else {
    Database::reinitializeConnection();
    $result = Database::getInstance()->getCredentials($_POST['login']);
    $row = pg_fetch_array($result);
    if (!$row) {
        $response_code = 403;
        $response = array('error' => "Пользователь с указанным логином не найден");

    }
    elseif (password_verify($_POST['password'], $row[1])) {
        $response_code = 200;
        session_regenerate_id();
        $_SESSION['ID'] = $row[0];
    }
    else {
        $response_code = 403;
        $response = array('error' => "Неверный пароль");
    }
    Database::getInstance()->freeResult($result);
}
http_response_code($response_code);
echo json_encode($response);
?>