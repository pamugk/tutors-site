const Main = {

    body: document.getElementById('body'),

    init() {
        this.body.style.background = 'url("../images/main-back2.jpg") no-repeat fixed';
    },

    goToRegistration() {
        window.location += 'registration';
    }
};

Main.init();