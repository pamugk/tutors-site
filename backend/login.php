<?php
include_once "../validator.class.php";

$response_code = 418;
$response = "";
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $response_code = 405;
    $response = array('error' => "Запрос не поддерживается");
}
else {
    $validationResult = Validator::validateLoginInfo($_POST);
    if ($validationResult) {
        $response_code = 403;
        $response = array('error' => $validationResult);
    }
    else {
        $response_code = 200;
    }
}
http_response_code($response_code);
echo json_encode($response)
?>