const TutorsList = {
    list: document.getElementById('tutors-list'),
    paging: document.getElementById('paging'),
    pageNum: 1,
    pageSize: 2,

    init() {
        this.getCount();
        this.getList();
    },

    async getCount() {
        fetch(`${PREFIX}backend/tutors.php?req=count`)
            .then(response => {
                console.log(response);

                if (response.status === 200) {
                    response.json()
                        .then(res => {
                            const count = res.count;
                            console.log(count);
                            this.paging.innerHTML = '';
                            console.log(Math.ceil(count/this.pageSize));
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
        fetch(`${PREFIX}backend/tutors.php?pageNum=${this.pageNum}&pageSize=${this.pageSize}`)
            .then(response => {
                if (response.status === 200) {
                    response.json()
                        .then(tutors => {
                            this.list.innerHTML = '';
                            for (let tutor of tutors) {
                                let li = document.createElement('li');
                                li.innerText = `${tutor.name} ${tutor.surname} ${tutor.patronymic}`;
                                this.list.appendChild(li);
                            }
                        });
                } else {
                    response.json().then(data => this.showError(data.error));
                }
            }).catch(error => console.log('Request failed', error));
    },

    changePage(pageNum, pageSize) {
        this.pageNum = pageNum;
        this.pageSize = pageSize;
        this.getList();
    }
};

TutorsList.init();