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
    Database::reinitializeConnection();
    if (Database::getInstance()->isTutor($_SESSION['ID'])) {
        if ($valResult = Validator::validateTutorInfo($_POST)) {
            $response_code = 400;
            $response = array('error' => $valResult);
        }
        else {
            $response_code = 200;
            Database::getInstance()->updateTutorInfo(array($_SESSION['ID'], 
            key_exists('about', $_POST) ? $_POST['about'] : '', $_POST['experience'], $_POST['price']));
            $selectedSubjects = $_POST['subjects'];
            Database::getInstance()->clearTutorSubjects($_SESSION['ID']);
            if (!empty($selectedSubjects))
                Database::getInstance()->updateTutorSubjects($_SESSION['ID'], $selectedSubjects);
        }
    }
    else {
        $response_code = 403;
        $response = array('error' => "Вы не являетесь репетитором!");
    }
}
else {
    $response_code = 401;
    $response = array('error' => "Не осуществлён вход");
}
http_response_code($response_code);
echo json_encode($response)
?>