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
        fetch(`http://localhost:8080/backend/login.php`, 
            { 
                method: 'post',
                body: new FormData(this.formElement)
            })
            .then(response => {
                if (response.status == 200)
                    window.location.replace("http://localhost:8080/");
                else response.json().then(data => this.showError(data.error));
            })
            .catch(error => console.log('Request failed', error));
    },
}

Login.init();