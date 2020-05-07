const Tutor = {
    errorElement: document.getElementById('error2'),
    tutorFormElement: document.getElementById('tutor-form'),

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
        fetch(`http://localhost:8080/backend/tutor.php`, 
            { 
                method: 'post',
                body: new FormData(this.tutorFormElement)
            })
            .then(response => {
                if (response.status == 200)
                    window.location.replace("http://localhost:8080/personal");
                else response.json().then(data => this.showError(data.error));
            })
            .catch(error => console.log('Request failed', error));
    },
}

Tutor.init();