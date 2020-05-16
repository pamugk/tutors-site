(function($) {
    "use strict"

    // Preloader
    $(window).on('load', function() {
        $("#preloader").delay(600).fadeOut();
    });

    // Mobile Toggle Btn
    $('.navbar-toggle').on('click',function(){
        $('#header').toggleClass('nav-collapse')
    });

    $('#myModal').on('shown.bs.modal', function () {
        $('#myInput').trigger('focus')
    })

})(jQuery);

function sendMessageToUs() {
    let msgForm = document.getElementById('msg-form');
    let spinner = document.getElementById('spinner-modal');
    let alert = document.getElementById('alert');

    spinner.classList.add('loader');
    fetch('/backend/contact.php',
        {
            method: 'post',
            body: new FormData(msgForm)
        })
        .then(response => {
            spinner.classList.remove('loader');
            if (response.status === 200) {
                alert.setAttribute('class', 'alert text-center alert-success mx-auto my-auto');
                alert.style.visibility = 'visible';
                alert.innerText="Сообщение отправлено, постараемся как можно скорее ответить тебе на указанный email!";
            }
            else response.json().then(data => this.showError(data.error));
        })
        .catch(error =>  {
            spinner.classList.remove('loader');
            alert.setAttribute('class', 'alert text-center alert-danger mx-auto my-auto');
            alert.style.visibility = 'visible';
            alert.innerText="Не удалось отправить сообщение! Попробуй еще раз";
        });
}