<?php

include_once "../../database.class.php";
include_once "../../validator.class.php";

$response_code = 418;
$response = "";

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    $response_code = 405;
    $response = array('error' => "Запрос не поддерживается");
} else {
    $result = null;
    if ($valResult = Validator::validatePageData($_GET)) {
        $response_code = 400;
        $response = array('error' => $valResult);
    } else {
        $search = $_GET['q'];
        $teachingSubject = $_GET['ts'];
        $pageNum = $_GET['n'];
        $pageSize = $_GET['s'];
        $order = $_GET['o'];

        Database::reinitializeConnection();
        $result = Database::getInstance()->getListTutors($pageNum, $pageSize, $search, $teachingSubject, $order);

        if ($result) {
            $response_code = 200;
            $response = $result;
        }
    }
}

http_response_code($response_code);
echo json_encode($response);