const Tutor = {
    errorElement: document.getElementById('error2'),
    tutorFormElement: document.getElementById('tutor-form'),
    spinnerElement: document.getElementById('spinner2'),

    init() {
        this.tutorFormElement.onsubmit = e => {
            e.preventDefault();
            this.tutor();
        };
    },

    showError(error) {
        this.errorElement.textContent = error;
        this.errorElement.style.display = '';
    },

    async tutor() {
        this.spinnerElement.classList.add('loader');
        fetch(`${PREFIX}backend/tutor.php`, 
            { 
                method: 'post',
                body: new FormData(this.tutorFormElement)
            })
            .then(response => {
                this.spinnerElement.classList.remove('loader');
                if (response.status == 200)
                    window.location.replace(`${PREFIX}personal`);
                else response.json().then(data => this.showError(data.error));
            })
            .catch(error =>  { 
                this.spinnerElement.classList.add('d-none'); console.log('Request failed', error) 
            });
    },
}

Tutor.init();