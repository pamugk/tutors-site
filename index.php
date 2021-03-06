<?php
include_once "database.class.php";
include_once "pgSessionHandler.class.php";
include_once "ui.class.php";

session_set_save_handler(PgSessionHandler::getInstance(), false);

Database::reinitializeConnection();
$result = Database::getInstance()->getPathPage(explode('?', $_SERVER['REQUEST_URI'], 2)[0]);
if (!$result)
    $page = "404";
else {
    $row = pg_fetch_array($result);
    $page = !$row ? "404" : $row[0];
    Database::getInstance()->freeResult($result);
}

//$countTutors = Database::getInstance()->getCountTutors();
//$tutors = Database::getInstance()->getListTutors(1, 10, null, null);

$bind = array(
    'user' => null, 
    'teachingSubjects' => Database::getInstance()->getAllTeachingSubjects()
);

session_start();
$id = key_exists('ID',$_SESSION) ? $_SESSION['ID'] : false;
session_write_close();
$loggedIn = $id !== false;

if ($loggedIn) {
    $user = Database::getInstance()->getUser($id);
    $bind['user'] = $user;
    $bind['avatar'] = $user['avatarId'] == null ? "/images/avatar.png" : "/backend/images.php?id=".$user['avatarId'];
}

switch ($page) {
    case 'personal': {
        if ($loggedIn && $user['isTutor']) {
            $bind['subjectsOfTutor'] = Database::getInstance()->checkSubjectsTaughtByTutor($id);
            $bind['tutorInfo'] = Database::getInstance()->getTutorInfo($id);
        }
        break;
    }
    case 'messages': {
        $bind['contacts'] = Database::getInstance()->fetchContacts($_SESSION['ID']);
    }
    case 'tutor' : {
        if (key_exists('id', $_GET)) {
            $fullTutorInfo = Database::getInstance()->getFullTutorInfo($_GET['id']);
            $bind['tutor'] = $fullTutorInfo['tutor'];
            if ($bind['tutor'] != null) {
                $bind['tutor'] = $fullTutorInfo['tutor'];
                $bind['tutorAvatar'] = $bind['tutor']['avatarId'] == null ? "/images/avatar.png" : "/backend/images.php?id=".$bind['tutor']['avatarId'];
                $bind['subjects'] = $fullTutorInfo['subjects'];
            }
        }
    }
}
$template = UI::constructPage($page, UI::getTitle($page), UI::getHeader(), UI::getSidebar(), UI::getContent($page), UI::getFooter());
print(UI::compilePage($template, $bind));
?>