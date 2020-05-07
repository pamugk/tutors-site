const Personal = {
    errorElement: document.getElementById('error'),
    formElement: document.getElementById('personal-form'),
    passwordElement: document.getElementById('password-input'),
    password2Element: document.getElementById('password2-input'),

    init() {
        this.formElement.onsubmit = e => {
            e.preventDefault();
            this.personal();
        };
    },

    showError(error) {
        this.errorElement.textContent = error;
        this.errorElement.style.display = '';
    },

    checked($changePwdCheckbox) {
        disablePwd = !$changePwdCheckbox.checked
        this.passwordElement.disabled = disablePwd
        this.password2Element.disabled = disablePwd
        if (disablePwd) {
            this.passwordElement.value = null
            this.password2Element.value = null
        }
    },

    async personal() {
        fetch(`http://localhost:8080/backend/personal.php`, 
            { 
                method: 'post',
                body: new FormData(this.formElement)
            })
            .then(response => {
                if (response.status == 200)
                    window.location.replace("http://localhost:8080/personal");
                else response.json().then(data => this.showError(data.error));
            })
            .catch(error => console.log('Request failed', error));
    },
}

Personal.init();