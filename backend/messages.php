<?php
include_once "../database.class.php";
include_once "../pgSessionHandler.class.php";
session_set_save_handler(PgSessionHandler::getInstance(), true);

session_start();
if (!key_exists('ID',$_SESSION)) {
    $response_code = 401;
    $response = array('error' => "Не осуществлён вход");
}
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!key_exists('i', $_GET)) {
        $response_code = 400;
        $response = array('error' => "Не указан корреспондент");
    }
    else {
        Database::reinitializeConnection();
        $messages = Database::getInstance()->messagesFetch($_SESSION['ID'], (int)$_GET['i']);
        if ($messages == FALSE) {
            $response_code = 500;
            $response = array('error' => "При получении что-то пошло не так");
        }
        else {
            $response_code = 200;
            $response = array('messages' =>$messages);
        }
    }
}
elseif($_SERVER['REQUEST_METHOD'] == 'POST')  {
    if (!key_exists('message', $_POST) || $_POST['message'] == '') {
        $response_code = 400;
        $response = array('error' => "Не указано сообщение");
    }
    elseif (!key_exists('i', $_GET)) {
        $response_code = 400;
        $response = array('error' => "Не указан получатель");
    }
    else {
        Database::reinitializeConnection();
        $id = Database::getInstance()->messageSend($_SESSION['ID'], $_GET['i'], $_POST['message']);
        if ($id === FALSE) {
            $response_code = 500;
            $response = array('error' => "При посылке сообщения что-то пошло не так");
        }
        else {
            $response_code = 201;
            $response = array('id' =>$id);
        }
    }
}
else {
    $response_code = 405;
    header("Allow: POST, GET");
    $response = array('error' => "Запрос не поддерживается");
}
http_response_code($response_code);
echo json_encode($response);
?>