<?php

include_once "../database.class.php";

header('Content-type: application/json; charset=utf-8');

$response_code = 418;
$response = "";
echo $bind;
if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    $response_code = 405;
    $response = array('error' => "Запрос не поддерживается");
} else {
    Database::reinitializeConnection();
    $result = null;
    if ($_GET['req'] == 'count') {
        $result = array('count' => Database::getInstance()->getCountTutors());
    } else {
        $pageNum = $_GET['pageNum'];
        $pageSize = $_GET['pageSize'];

        $result = Database::getInstance()->getListTutors($pageNum, $pageSize);
    }

    if ($result) {
        $response_code = 200;
        $response = $result;
    }
}

http_response_code($response_code);
echo json_encode($response);