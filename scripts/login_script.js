const Login = {
    errorElement: document.getElementById('error'),
    formElement: document.getElementById('login-form'),
    spinnerElement: document.getElementById('spinner'),

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
        this.spinnerElement.classList.add('loader');
        fetch(`${PREFIX}backend/login.php`,
            { 
                method: 'post',
                body: new FormData(this.formElement)
            })
            .then(response => {
                this.spinnerElement.classList.remove('loader');
                if (response.status === 200)
                    window.location.replace(PREFIX);
                else response.json().then(data => this.showError(data.error));
            })
            .catch(error =>  { 
                this.spinnerElement.classList.add('d-none'); console.log('Request failed', error) 
            });
    },
};

Login.init();