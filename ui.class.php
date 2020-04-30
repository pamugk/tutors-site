<?php
class UI {
    private static function getMainPage() {
        return file_get_contents(PATH."/ui/mainpage.html");
    }

    private static function getNotfoundPage() {
        return file_get_contents(PATH."ui/notfound.html");
    }

    private static function getLoginPage() {
        
    }

    private static function getTutorsPage() {
        
    }

    public static function constructPage($title, $header, $sidebar, $content, $footer) {
        return 
            "<!DOCTYPE html>
            <html>
                <head>
                    <meta charset=\"utf-8\" />
                    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
                    <title>$title</title>
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
                    <div class=\"page-content\">
                        <main class=\"main-content container\">
                            $content
                        </main>
                    </div>
                </body>
                <footer class=\"footer\">
                    $footer
                </footer>
            </html>";
    }

    public static function getContent($page) {
        switch ($page) {
            case "main":
                return self::getMainPage();
            default:
                return self::getNotfoundPage();
        }
    }

    public static function getFooter() {
        return 
        '<div class="container">
            <p>Пермь, ПГНИУ, 30 апреля 2020.<br>Склёпано на коленке криворуким студентом© по best practice.</p>
        </div>';
    }

    public static function getHeader() {
        return "<p>Header</p>";
    }

    public static function getSidebar() {
        return "";
    }
}
?>