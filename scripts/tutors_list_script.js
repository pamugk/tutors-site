const TutorsList = {

    list: document.getElementById('tutors-list'),
    paging: document.getElementById('paging'),
    spinner: document.getElementById('spinner'),
    btnSearch: document.getElementById('search-btn'),
    inpSearch: document.getElementById('search-inp'),
    orderSelect: document.getElementById('order-select'),
    subjectSelect: document.getElementById('subject-select'),

    pageNum: 1,
    pageSize: 2,

    async init() {
        await this.getCount();
        await this.getList();
    },

    async getCount() {
        fetch(`${PREFIX}backend/tutors/count.php`)
            .then(response => {
                console.log(response);

                if (response.status === 200) {
                    response.text()
                        .then(res => {
                            const count = parseInt(res);
                            this.paging.innerHTML = '';
                            for (let i = 0; i < Math.ceil(count/this.pageSize); i++) {
                                let li = document.createElement('li');
                                li.innerHTML = `<a href="#" onclick="TutorsList.changePage(${i+1}, ${this.pageSize})">${i+1}</a>`;
                                this.paging.appendChild(li);
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
        fetch(`${PREFIX}backend/tutors/list.php?&n=1&s=10&q=${this.inpSearch.value}&o=${this.orderSelect.options[this.orderSelect.selectedIndex].value}&ts=${this.subjectSelect.options[this.subjectSelect.selectedIndex].value}`)
            .then(response => {
                if (response.status === 200) {
                    response.json()
                        .then(tutors => {
                            console.log(tutors);
                            let i = 0;
                            for (let tutor of tutors) {
                                i++;
                                let tsConcat = '';
                                for (let ts of tutor.ts) {
                                    tsConcat += ts + '<br>';
                                }
                                this.list.innerHTML += `
                                    <div class="card-tutor">
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
                                            <div class="card-header" style="background-color: transparent; color: darkred;">
                                                Оплата за 1 час
                                            </div>
                                            <div class="card-body" style="color: #1c7430; font-size: 30px;">
                                                ${tutor.price}
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

    async changePage(pageNum, pageSize) {
        this.pageNum = pageNum;
        this.pageSize = pageSize;
        await this.getList();
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
    }
};

TutorsList.init();