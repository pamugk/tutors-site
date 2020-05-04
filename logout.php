<?php
session_start();
session_unset();
session_regenerate_id(true);
header("Location: /", true, 301);
die();
?>