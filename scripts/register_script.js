const Register = {
    errorElement: document.getElementById('error'),
    formElement: document.getElementById('registration-form'),
    
    init() {
        this.formElement.onsubmit = e => {
            e.preventDefault();
            this.registrate();
        };
    },

    showError(error) {
        this.errorElement.textContent = error;
        this.errorElement.style.display = '';
    },

    async registrate() {
        fetch(`${PREFIX}backend/registration.php`,
            { 
                method: 'post',
                body: new FormData(this.formElement)
            })
            .then(response => {
                if (response.status == 201)
                    window.location.replace(PREFIX);
                else response.json().then(data => this.showError(data.error));
            })
            .catch(error => console.log('Request failed', error));
    },
}

Register.init();