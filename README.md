# Сайт "Биржа репетиторов"

## О сайте

Сайт был разработан как совместный проект с @izimin (его [профиль](https://github.com/izimin "Ссылка на профиль пользователя izimin")) в образовательных целях.  
На сайте реализована возможность регистрации пользователей (которые могут выступать как репетиторами, так и обучающимися), изменения информации о пользователях (в личном кабинете), поиска по репетиторам, а также переписки между репетиторами и обучающимися. Бонусом имеется возможность отправки сообщения разработчиками для обратной связи, которое, возможно, когда-нибудь даже будет рассмотрено (нет).

## Подробности реализации

Сайт разработан на языке PHP со вкраплениями JavaScript (в первую очередь, для реализации AJAX).  
Управление зависимостями реализовано с помощью [Composer](https://getcomposer.org/ "Сайт Composer").  
Зависимости как таковых всего две: расширение для работы с БД PostgreSQL и шаблонизатор [Smarty](https://www.smarty.net/ "Ссылка на сайт Smarty").  
Для хранения данных используется PostgreSQL, в которой помимо собственно таблиц для хранения данных также имеются триггеры и хранимые процедуры.
