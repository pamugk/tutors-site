const Personal = {
    errorElement: document.getElementById('error'),
    imageElement: document.getElementById('avatarImg'),
    formElement: document.getElementById('personal-form'),
    passwordElement: document.getElementById('password-input'),
    password2Element: document.getElementById('password2-input'),
    spinnerElement: document.getElementById('spinner1'),

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

    checked(changePwdCheckbox) {
        disablePwd = !changePwdCheckbox.checked
        this.passwordElement.disabled = disablePwd
        this.password2Element.disabled = disablePwd
        if (disablePwd) {
            this.passwordElement.value = null
            this.password2Element.value = null
        }
    },

    updImage(input) {
        var reader = new FileReader();
        var img = this.imageElement;
        reader.onloadend = function() {
            img.src = reader.result;
        }
        reader.readAsDataURL(input.files[0]);
    },

    async personal() {
        this.spinnerElement.classList.remove('d-none');
        fetch(`${PREFIX}backend/personal.php`, 
            { 
                method: 'post',
                body: new FormData(this.formElement)
            })
            .then(response => {
                this.spinnerElement.classList.add('d-none');
                if (response.status == 200)
                    window.location.replace(`${PREFIX}personal`);
                else response.json().then(data => this.showError(data.error));
            })
            .catch(error =>  { 
                this.spinnerElement.classList.add('d-none'); console.log('Request failed', error) 
            });
    },
}

Personal.init();