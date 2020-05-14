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

    pageNum: 1,
    pageSize: 5,
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
                            console.log(tutors);
                            let i = 0;
                            this.tutors = tutors;
                            for (let tutor of tutors) {
                                i++;
                                let tsConcat = '';
                                for (let ts of tutor.ts) {
                                    tsConcat += ts + '<br>';
                                }
                                this.list.innerHTML += `
                                    <div class="card-tutor" onclick="TutorsList.goToCard(${i-1})">
                                        <div style="width: 180px; padding-right: 30px;">
                                            <img src=${tutor.image} id="img-tutor" width="150" onload="TutorsList.stopSpinner(${i})"> 
                                                <div class="loader-img" id="spinner${i}"></div> 
                                            <img/>
                                        </div>
                                        <div class="data-tutor">
                                            <div class="row-data">
                                                <label class="label">Имя:</label>
                                                <label class="label-value">${tutor.name}</label>
                                            </div>
                                            <div class="row-data">
                                                <label class="label">Фамилия:</label>
                                                <label class="label-value">${tutor.surname}</label>
                                            </div>
                                            <div class="row-data">
                                                <label class="label">Отчество:</label>
                                                <label class="label-value">${tutor.patronymic}</label>
                                            </div>
                                            <div class="row-data">
                                                <label class="label">Стаж:</label>
                                                <label class="label-value">${this.experienceToStr(tutor.experience)}</label>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="row-data" style="margin-left: 20px;">
                                                <label class="label" style="height: 150px;">Предметы:</label>
                                                <label class="label-value">${tsConcat}</label>
                                            </div>
                                        </div>
                                        <div class="tutor-price">
                                            <div class="card-header" style="background-color: transparent; color: darkred; padding-right: 0">
                                                Оплата за 1 час
                                            </div>
                                            <div class="card-body" style="color: #1c7430; font-size: 30px; width: 100%">
                                                ${tutor.price} руб.
                                            </div>
                                        </div>
                                    </div>`;
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
        console.log(this.countPages);
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
    }
};

TutorsList.init();