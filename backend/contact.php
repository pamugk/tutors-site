<?php
include_once "../database.class.php";
include_once "../validator.class.php";

$response_code = 418;
$response = "";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $response_code = 405;
    header("Allow: POST");
    $response = array('error' => "Запрос не поддерживается");
}
else {
    if ($valResult = Validator::validateContactInfo($_POST)) {
        $response_code = 400;
        $response = array('error' => $valResult);
    } else {
        Database::reinitializeConnection();
        $result = Database::getInstance()->contactUs($_POST['email'], $_POST['msg']);
        if ($result) {
            $response = $result;
            $response_code = 200;
        }
    }
}

http_response_code($response_code);
echo json_encode($response);
?>