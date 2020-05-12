<?php

include_once "../../database.class.php";

$response_code = 418;
$response = "";

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    $response_code = 405;
    $response = array('error' => "Запрос не поддерживается");
} else {
    Database::reinitializeConnection();
    $result = Database::getInstance()->getCountTutors();

    if ($result) {
        $response_code = 200;
        $response = $result;
    }
}

http_response_code($response_code);
echo $response;