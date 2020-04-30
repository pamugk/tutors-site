<?php
include "database.class.php";
include "ui.class.php";

Database::reinitializeConnection();
$result = Database::getInstance()->getPathPage($_SERVER["REQUEST_URI"]);
if (!$result)
    $page = "404";
else {
    $row = pg_fetch_array($result);
    $page = !$row ? "404" : $row[0];
    Database::getInstance()->freeResult($result);
}
print(UI::constructPage("Title", UI::getHeader(), UI::getSidebar(), UI::getContent($page), UI::getFooter()));
?>