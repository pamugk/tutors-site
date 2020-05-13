const Register = {
    errorElement: document.getElementById('error'),
    formElement: document.getElementById('registration-form'),
    spinnerElement: document.getElementById('spinner'),
    
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
        this.spinnerElement.classList.add('loader');
        fetch('/backend/registration.php',
            { 
                method: 'post',
                body: new FormData(this.formElement)
            })
            .then(response => {
                this.spinnerElement.classList.remove('loader');
                if (response.status === 201)
                    window.location.replace('/');
                else response.json().then(data => this.showError(data.error));
            })
            .catch(error =>  { 
                this.spinnerElement.classList.add('d-none'); console.log('Request failed', error) 
            });
    },
};

Register.init();