<?php
include_once "../database.class.php";
session_start();
Database::reinitializeConnection();
Database::getInstance()->stopSession($_SESSION['ID']);
session_destroy();
header("Location: /", true, 301);
exit();
?>