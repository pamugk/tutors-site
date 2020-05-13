<?php
include_once "pgSessionHandler.class.php";
session_set_save_handler(PgSessionHandler::getInstance(), true);

session_start();
session_unset();
session_regenerate_id();
header("Location: /", true, 301);
die();
?>