const Login = {
    errorElement: document.getElementById('error'),
    formElement: document.getElementById('login-form'),

    init() {
        this.formElement.onsubmit = e => {
            e.preventDefault();
            this.login();
        };
    },

    showError(error) {
        this.errorElement.textContent = error;
        this.errorElement.style.display = '';
    },

    async login() {
        fetch(`${PREFIX}backend/login.php`,
            { 
                method: 'post',
                body: new FormData(this.formElement)
            })
            .then(response => {
                if (response.status === 200)
                    window.location.replace(PREFIX);
                else response.json().then(data => this.showError(data.error));
            })
            .catch(error => console.log('Request failed', error));
    },
};

Login.init();