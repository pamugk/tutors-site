<?php
include_once "../database.class.php";
include_once "../validator.class.php";

$response_code = 418;
$response = "";
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $response_code = 405;
    $response = array('error' => "Запрос не поддерживается");
}
else {
    $validationResult = Validator::validateRegistrationInfo($_POST);
    if ($validationResult) {
        $response_code = 400;
        $response = array('error' => $validationResult);
    }
    else {
        $response_code = 201;
        $pwd = password_hash($_POST['password'], PASSWORD_BCRYPT);
        Database::reinitializeConnection();
        try{
            Database::getInstance()->registerUser(
                array($_POST['login'], $pwd, $_POST['name'], $_POST['surname'], 
                array_key_exists('patronymic', $_POST) ? $_POST['patronymic'] : null,
                array_key_exists('isTutor', $_POST) ? 'true' : 'false')
            );
        }
        catch(Exception $e) {
            $response_code = 500;
            $response = array('error' => 'Что-то пошло не так');
        }
    }
}
http_response_code($response_code);
echo json_encode($response)
?>