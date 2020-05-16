const Home = {

    list: document.getElementById('tutors'),
    body: document.getElementById('body'),
    photo: document.getElementById('photo'),
    photoModal: document.getElementById('photo-modal'),

    async init() {
        await this.getList();
    },

    async getList() {
        this.list.innerHTML = '';
        await fetch(`/backend/tutors/list.php?n=1&s=4&q=&o=3&ts=0`)
            .then(response => {
                if (response.status === 200) {
                    response.json()
                        .then(tutors => {
                            this.list.innerHTML = '';
                            let i = 0;
                            this.tutors = tutors;
                            for (let tutor of tutors) {
                                i++;
                                const fullName = `${tutor.surname} ${tutor.name} ${tutor.patronymic}`;
                                this.list.innerHTML += `
                                     <div class="col-md-3 col-sm-6 col-xs-6">
                                        <div class="tutor-card">
                                            <div>
                                                <a class="tutor-home-img" onclick="Home.showPhoto('${tutor.image}')"  style="overflow: hidden; width: 255px;  height: 255px;">
                                                    <img src="${tutor.image}" alt="">
                                                    <i class="course-link-icon fa fa-search-plus" style="border: 0"></i>
                                                </a>
                                            </div>
                                            <a class="tutor-home-title" href="/tutor?id=${tutor.id}">${fullName}</a>
                                            <div class="tutor-home-details">
                                                <span class="course-category">Оплата за час</span>
                                                <span class="tutor-home-price price">${tutor.price} руб</span>
                                            </div>
                                        </div>
                                    </div>
                                    `
                            }
                        });
                } else {
                    response.json().then(data => this.showError(data.error));
                }
            }).catch(error => console.log('Request failed', error));
    },

    showPhoto(url) {
        this.photo.setAttribute('src', url);
        this.photoModal.style.visibility='visible';
        document.getElementById('myModal').setAttribute('aria-hidden', 'false');
    },

    closePhoto() {
        this.photoModal.style.visibility='hidden';
    }
};

Home.init();