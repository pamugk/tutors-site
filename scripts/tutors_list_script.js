const TutorsList = {

    list: document.getElementById('tutors-list'),
    pagination: document.getElementById('pagination'),
    spinner: document.getElementById('spinner'),
    btnSearch: document.getElementById('search-btn'),
    inpSearch: document.getElementById('search-inp'),
    orderSelect: document.getElementById('order-select'),
    subjectSelect: document.getElementById('subject-select'),
    countTutorsSelect: document.getElementById('count-tutors-select'),
    nextLi: document.getElementById('next-li'),
    prevLi: document.getElementById('prev-li'),
    linkPrev: document.getElementById('link-prev'),
    linkNext: document.getElementById('link-next'),
    photo: document.getElementById('photo'),
    photoModal: document.getElementById('photo-modal'),

    pageNum: 1,
    pageSize: 4,
    countPages: 1,
    tutors: null,

    async init() {
        this.getCount();
        await this.getList();
    },

    async getCount() {
        await fetch(`/backend/tutors/count.php?q=${this.inpSearch.value}&ts=${this.subjectSelect.options[this.subjectSelect.selectedIndex].value}`)
            .then(response => {
                if (response.status === 200) {
                    response.text()
                        .then(res => {
                            const count = parseInt(res);
                            this.countPages = Math.ceil(count/this.pageSize);
                            this.changePage(1);
                            this.pagination.innerHTML = '';
                            for (let i = 0; i < this.countPages; i++) {
                                let li = document.createElement('li');
                                li.classList.add('paging-page');
                                li.innerHTML = `<a class="paging-page-link" id="paging-item${i+1}" href="#" onclick="TutorsList.changePage(${i+1})">${i+1}</a>`;
                                this.pagination.appendChild(li);
                                if (i === 0) {
                                    document.getElementById(`paging-item${i+1}`).classList.add('active');
                                }
                            }
                        });
                } else {
                    response.json().then(data => this.showError(data.error));
                }
            }).catch(error => console.log('Request failed', error));
    },

    async getList() {
        this.list.innerHTML = '';
        this.spinner.classList.add('loader');
        await fetch(`/backend/tutors/list.php?n=${this.pageNum}&s=${this.pageSize}&q=${this.inpSearch.value}&o=${this.orderSelect.options[this.orderSelect.selectedIndex].value}&ts=${this.subjectSelect.options[this.subjectSelect.selectedIndex].value}`)
            .then(response => {
                if (response.status === 200) {
                    response.json()
                        .then(tutors => {
                            this.list.innerHTML = '';
                            let i = 0;
                            this.tutors = tutors;
                            for (let tutor of tutors) {
                                i++;
                                let tsConcat = '';
                                for (let ts of tutor.ts) {
                                    tsConcat += ts + '<br>';
                                }
                                const fullName = `${tutor.surname} ${tutor.name} ${tutor.patronymic}`;
                                const href = `/tutor?id=${tutor.id}`;
                                this.list.innerHTML += `
                                     <div class="col-md-3 col-sm-6 col-xs-6" style="background: #DBDBDB; border-bottom: 1px solid grey">
                                        <div class="tutor-card">
                                            <div>
                                                <a class="tutor-home-img" onclick="TutorsList.showPhoto('${tutor.image}')"  style="overflow: hidden; width: 255px;  height: 255px;">
                                                    <img src="${tutor.image}" id="img-tutor" width="150" onload="TutorsList.stopSpinner(${i})" alt=""> 
                                                        <div class="loader-img" id="spinner${i}"></div> 
                                                    </img>
                                                    <i class="course-link-icon fa fa-search-plus" style="border: 0"></i>
                                                </a>
                                            </div>
                                            <a class="tutor-home-title" href="${href}">${fullName}</a>
                                            <div class="tutor-home-details">
                                                <span class="category">Стаж</span>
                                                <span class="tutor-home-price exp">${this.experienceToStr(tutor.experience)}</span>
                                            </div>
                                            <div class="tutor-home-details">
                                                <span class="category">Оплата за час</span>
                                                <span class="tutor-home-price price">${tutor.price} руб</span>
                                            </div>
                                            <div class="tutor-home-details">
                                                ${tsConcat}
                                            </div>
                                        </div>
                                    </div>
                                    `;
                            }
                            this.spinner.classList.remove('loader');
                        });
                } else {
                    response.json().then(data => this.showError(data.error));
                }
            }).catch(error => console.log('Request failed', error));
    },

    experienceToStr(experience) {
        let str;
        let count = experience % 100;
        if (count >= 5 && count <= 20) {
            str = 'лет';
        } else {
            count = count % 10;
            if (count === 1) {
                str = 'год';
            } else if (count >= 2 && count <= 4) {
                str = 'года';
            } else {
                str = 'лет';
            }
        }
        return experience + " " + str;
    },

    stopSpinner(spinnerNum) {
        document.getElementById('spinner' + spinnerNum).classList.remove('loader-img');
    },

    async changePage(pageNum) {
        if (this.pageNum === pageNum) {
            return;
        }
        this.pageNum = pageNum;
        for (let i = 0; i < this.countPages; i++) {
            document.getElementById(`paging-item${i + 1}`).classList.remove('active');
        }
        document.getElementById(`paging-item${pageNum}`).classList.add('active');
        if (pageNum > 1 && pageNum < this.countPages) {
            this.changeStyles(this.linkPrev, false);
            this.changeStyles(this.linkNext, false);
        } else if (this.countPages === 1) {
            this.changeStyles(this.linkPrev, true);
            this.changeStyles(this.linkNext, true);
        } else if (pageNum === 1) {
            this.changeStyles(this.linkPrev, true);
            this.changeStyles(this.linkNext, false);
        } else if (pageNum === this.countPages) {
            this.changeStyles(this.linkNext, true);
            this.changeStyles(this.linkPrev, false);
        }

        await this.getList();
    },

    changeStyles(item, isDisable) {
        if (isDisable) {
            item.removeAttribute('href');
            item.classList.replace('paging-page-link', 'disabled-li');
        } else {
            item.setAttribute('href', '#');
            item.classList.replace('disabled-li', 'paging-page-link');
        }
    },

    async prevPage() {
        if (this.pageNum - 1 < 1) {
            return;
        }
        await this.changePage(this.pageNum - 1);
    },

    async nextPage() {
        if (this.pageNum + 1 > this.countPages) {
            return;
        }
        await this.changePage(this.pageNum + 1);
    },

    async changeCountTutors() {
        this.pageSize = this.countTutorsSelect.options[this.countTutorsSelect.selectedIndex].value;
        this.getCount();
        await this.getList();
    },

    async search() {
        this.getCount();
        await this.getList();
    },

    goToCard(tutorIdx) {
        global_tutor = this.tutors[tutorIdx];
        window.location.replace('tutor');
    },

    showPhoto(url) {
        this.photo.setAttribute('src', url);
        this.photoModal.style.visibility='visible';
    },

    closePhoto() {
        this.photoModal.style.visibility = 'hidden';
    },
};

TutorsList.init();