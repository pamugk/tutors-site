const Card = {

    img: document.getElementById('img-tutor'),
    tutor: global_tutor,

    init() {
        img.setAttribute('src', this.tutor.image);
    },
};

Card.init();