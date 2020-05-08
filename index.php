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

$bind = array('user' => null);

$loggedIn = key_exists('ID',$_SESSION);

if ($loggedIn) {
    $user = Database::getInstance()->getUser($_SESSION['ID']);
    $bind['user'] = $user;
    $bind['avatar'] = $user['avatarId'] == null ? "/images/avatar.png" : "/backend/images.php?id=".$user['avatarId'];
}

switch ($page) {
    case 'personal': {
        if ($loggedIn && $user['isTutor']) {
            $bind['subjectsOfTutor'] = Database::getInstance()->checkSubjectsTaughtByTutor($_SESSION['ID']);
            $bind['tutorInfo'] = Database::getInstance()->getTutorInfo($_SESSION['ID']);
        }
        break;
    }
}

$template = UI::constructPage(UI::getTitle($page), UI::getHeader(), UI::getSidebar(), UI::getContent($page), UI::getFooter());
print(UI::compilePage($template, $bind));
?>