<?php
include "database.class.php";
include "ui.class.php";
session_start();
Database::reinitializeConnection();
$result = Database::getInstance()->getPathPage($_SERVER["REQUEST_URI"]);
if (!$result)
    $page = "404";
else {
    $row = pg_fetch_array($result);
    $page = !$row ? "404" : $row[0];
    Database::getInstance()->freeResult($result);
}
$user = null;
if (key_exists('ID',$_SESSION)) {
    $result = Database::getInstance()->getUserBySession($_SESSION['ID']);
    if ($result) {
        $row = pg_fetch_array($result);
        $user = array("id" => $row[0], "login" => $row[1], "name" => $row[2], "surname" => $row[3], 
        "patronymic" => $row[4], "isTutor" => $row[5]);
        Database::getInstance()->freeResult($result);
    }
}
$template = UI::constructPage("Title", UI::getHeader(), UI::getSidebar(), UI::getContent($page), UI::getFooter());
print(UI::compilePage($template, array('user' => $user)));
?>