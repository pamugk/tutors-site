<?php
session_start();
session_unset();
session_regenerate_id();
session_write_close();
header("Location: /", true, 301);
die();
?>