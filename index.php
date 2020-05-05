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
$bind = array('user' => null);

if (key_exists('ID',$_SESSION)) {
    $user = Database::getInstance()->getUser($_SESSION['ID']);
    $bind['user'] = $user;
}
$template = UI::constructPage("Title", UI::getHeader(), UI::getSidebar(), UI::getContent($page), UI::getFooter());
print(UI::compilePage($template, $bind));
?>