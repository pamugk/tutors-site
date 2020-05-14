<?php
require('vendor/autoload.php');

class UI {
    private static function getAboutPage() {
        return file_get_contents(PATH."/ui/aboutpage.html");
    }

    private static function getMainPage() {
        return file_get_contents(PATH."/ui/mainpage.html");
    }

    private static function getNotfoundPage() {
        return file_get_contents(PATH."/ui/notfoundpage.html");
    }

    private static function getLoginPage() {
        return file_get_contents(PATH."/ui/loginpage.html");
    }

    private static function getRegistrationPage() {
        return file_get_contents(PATH."/ui/registrationpage.html");
    }

    private static function getPersonalPage() {
        return file_get_contents(PATH."/ui/personalpage.html");
    }

    private static function getTutorPage() {
        return file_get_contents(PATH."/ui/tutorpage.html");
    }

    private static function getTutorsListPage() {
        return file_get_contents(PATH."/ui/tutorslistpage.html");
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
                <body id='body'>
                    <script>let global_tutor = null</script>
                    <header>
                        $header
                    </header>
                    <nav class=\"sidebar\">
                        $sidebar
                    </nav>
                    <main class=\"main-content\">
                        $content
                    </main>
                    <footer class=\"footer\">
                        $footer
                    </footer>
                </body>
                <script defer src=\"/scripts/fontawesome/solid.min.js\"></script>
                <script defer src=\"/scripts/fontawesome/fontawesome.min.js\"></script>
            </html>";
    }

    public static function getContent($page) {
        switch ($page) {
            case "about":
                return self::getAboutPage();
            case "login":
                return self::getLoginPage();
            case "main":
                return self::getMainPage();
            case "personal":
                return self::getPersonalPage();
            case "registration":
                return self::getRegistrationPage();
            case "tutor":
                return self::getTutorPage();
            case "tutors":
                return self::getTutorsListPage();
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

    public static function getTitle($page) {
        switch ($page) {
            case "about":
                return 'О сайте';
            case "login":
                return 'Вход';
            case "main":
                return 'Главная страница';
            case "personal":
                return 'Личный кабинет';
            case "registration":
                return 'Регистрация';
            case "tutor":
                return 'Личная страница';
            case "tutors":
                return 'Список репетиторов';
            default:
                return 'Страница не найдена';
        }
    }
}
?>