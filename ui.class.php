<?php
require('vendor/autoload.php');

class UI {
    private static function getAboutPage() {
        return file_get_contents(PATH."/ui/aboutpage.html");
    }

    private static function getMainPage() {
        return file_get_contents(PATH."/ui/mainpage.html");
    }

    private static function getMessagesPage() {
        return file_get_contents(PATH."/ui/messagespage.html");
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

    public static function constructPage($page, $title, $header, $sidebar, $content, $footer) {
        return
            "<!DOCTYPE html>
            <html>
                <head>
                    <meta charset=\"utf-8\"/>
                    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
                    <title>$title</title>
                    <link rel=\"stylesheet\" href=\"styles/bootstrap.min.css\">
                    <link rel=\"stylesheet\" href=\"styles/bootstrap.min.old.css\">
                    <link rel=\"stylesheet\" href=\"styles/normalize.css\"/>
                    <link rel=\"shortcut icon\" href=\"images/favicon.ico\" type=\"image/x-icon\">
                    <!-- Google font -->
                    <link href=\"https://fonts.googleapis.com/css?family=Lato:700%7CMontserrat:400,600\" rel=\"stylesheet\">
                
                    <!-- Font Awesome Icon -->
                    <link rel=\"stylesheet\" href=\"styles/font-awesome.min.css\">
                
                    <!-- Custom stlylesheet -->
                    <link type=\"text/css\" rel=\"stylesheet\" href=\"styles/styles.css\"/>   
                </head>
                <body id='body'>
                    $header
                    $sidebar
                    $content
                    $footer
                    <div></div>
                    
                    <!-- preloader -->
                    <div id='preloader'><div class='preloader'></div></div>
                    <!-- /preloader -->
                    
                    <!-- contact us --> 
                    <div class=\"modal fade\" id=\"modal-contact-us\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"exampleModalLabel\" aria-hidden=\"true\">
                        <div class=\"modal-dialog\" role=\"document\">
                            <div class=\"modal-content\">
                                <div class=\"modal-header\">
                                    <h5 class=\"modal-title\" id=\"exampleModalLabel\">Обратная связь</h5>
                                    <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                                        <span aria-hidden=\"true\">&times;</span>
                                    </button>
                                </div>
                                <form id=\"msg-form\" onsubmit='sendMessageToUs()' >
                                    <div class=\"modal-body\">        
                                        <div id=\"alert\" style='visibility: hidden'></div>
                                        <div id='spinner-modal'></div>
                                        <div class=\"form-group\">
                                            <label for=\"email-input\">Твой e-mail:</label>
                                            <input type=\"email\" pattern=\".+@.+\" name=\"email\" class=\"form-control\" id=\"email-input\" placeholder=\"E-mail\" required>
                                        </div>
                                        <div class=\"form-group\">
                                            <label for=\"msg-input\">Твое сообщение:</label>
                                            <textarea class=\"form-control\" style='border-color:#EBEBEB;' id=\"msg-input\" name=\"msg\" placeholder=\"Текст сообщения\"
                                                      type=\"text\" required></textarea>
                                        </div>
                        
                                        <div class=\"modal-footer\">
                                            <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Отмена</button>
                                            <button type='submit' id=\"btn-send-msg\" type=\"button\" class=\"btn btn-primary\">Отправить</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /contact us --> 
                    
                </body>
                <script defer src=\"/scripts/fontawesome/solid.min.js\"></script>
                <script defer src=\"/scripts/fontawesome/fontawesome.min.js\"></script>
                <script type=\"text/javascript\" src=\"scripts/jquery.min.js\"></script>
                <script type=\"text/javascript\" src=\"scripts/fontawesome/bootstrap.min.js\"></script>
                <script type=\"text/javascript\" src=\"scripts/main_script.js\"></script>
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
            case "messages":
                return self::getMessagesPage();
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
            case "messages":
                return "Сообщения";
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