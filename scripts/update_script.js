const Update = {
    errorElement: document.getElementById('error'),
    formElement: document.getElementById('personal-form'),
    passwordElement: document.getElementById('password-input'),
    password2Element: document.getElementById('password2-input'),

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

    checked($changePwdCheckbox) {
        disablePwd = !$changePwdCheckbox.checked
        passwordElement.disabled = disablePwd
        password2Element.disabled = disablePwd
        if (disablePwd) {
            passwordElement.value = null
            password2Element.value = null
        }
    },

    async update() {
        fetch(`http://localhost:8080/backend/update.php`, 
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

Update.init();