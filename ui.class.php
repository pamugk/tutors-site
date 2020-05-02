<?php
require_once PATH."/libs/Smarty.class.php";

class UI {
    private static function getMainPage() {
        return file_get_contents(PATH."/ui/mainpage.html");
    }

    private static function getNotfoundPage() {
        return file_get_contents(PATH."/ui/notfound.html");
    }

    private static function getLoginPage() {
        return file_get_contents(PATH."/ui/loginpage.html");
    }

    private static function getRegistrationPage() {
        return file_get_contents(PATH."/ui/registrationpage.html");
    }

    private static function getTutorsPage() {
        
    }

    public static function compilePage($page, $parameters) {
        $smarty = new Smarty();
        foreach ($parameters as $name=>$value)
            $smarty->assign($name, $value);
        return $smarty->fetch("eval:$page");
    }

    public static function constructPage($title, $header, $sidebar, $content, $footer) {
        return 
            "<!DOCTYPE html>
            <html>
                <head>
                    <meta charset=\"utf-8\"/>
                    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
                    <title>$title</title>
                    <link rel=\"stylesheet\" href=\"styles/bootstrap.min.css\">
                    <link rel=\"stylesheet\" href=\"styles/normalize.css\"/>
                    <link rel=\"stylesheet\" href=\"styles/style.css\"/>
                </head>
                <header>
                    $header
                </header>
                <body>
                    <nav class=\"sidebar\">
                        $sidebar
                    </nav>
                    <main class=\"main-content container\">
                        $content
                    </main>
                </body>
                <footer class=\"footer\">
                    $footer
                </footer>
                <script defer src=\"/scripts/fontawesome/solid.min.js\"></script>
                <script defer src=\"/scripts/fontawesome/fontawesome.min.js\"></script>
            </html>";
    }

    public static function getContent($page) {
        switch ($page) {
            case "main":
                return self::getMainPage();
            case "login":
                return self::getLoginPage();
            case "registration":
                return self::getRegistrationPage();
            default:
                return self::getNotfoundPage();
        }
    }

    public static function getFooter() {
        return file_get_contents(PATH."/ui/footer.html");
    }

    public static function getHeader() {
        return file_get_contents(PATH."/ui/header.html");
    }

    public static function getSidebar() {
        return "";
    }
}
?>