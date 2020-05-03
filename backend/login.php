<?php
include_once "../database.class.php";
include_once "../validator.class.php";
session_start();
$response_code = 418;
$response = "";
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $response_code = 405;
    $response = array('error' => "Запрос не поддерживается");
}
else {
    $validationResult = Validator::validateLoginInfo($_POST);
    if ($validationResult) {
        $response_code = 400;
        $response = array('error' => $validationResult);
    }
    else {
        Database::reinitializeConnection();
        $result = Database::getInstance()->getCredentials($_POST['login']);
        $row = pg_fetch_array($result);
        Database::getInstance()->freeResult($result);
        if (!$row) {
            $response_code = 403;
            $response = array('error' => "Пользователь с указанным логином не найден");

        }
        elseif (password_verify($_POST['password'], $row[1])) {
            $response_code = 200;
            $result = Database::getInstance()->startSession($row[0]);
            $row = pg_fetch_array($result);
            $_SESSION['ID'] = $row[0];
        }
        else {
            $response_code = 403;
            $response = array('error' => "Неверный пароль");
        }
    }
}
http_response_code($response_code);
echo json_encode($response)
?>