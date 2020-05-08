<?php
include_once "../database.class.php";
$image = null;
if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    $response_code = 405;
    header("Allow: GET");
}
elseif (key_exists('id', $_GET)) {
    Database::reinitializeConnection();
    $image = Database::getInstance()->getImage($_GET['id']);
}
echo $image ? $image : file_get_contents('../images/avatar.png');
?>