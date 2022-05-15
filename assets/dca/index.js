import {Modal} from 'bootstrap';

console.log("index dca.");

const showModal = (e) => {
    e.preventDefault();
    myModal.show();
};

const goToGenerateDca = (e) => {
    e.preventDefault();
    const path = e.currentTarget.dataset.path;
    const valueDca = document.getElementById('dca_value').value;
    window.location.href = path + '/' + valueDca ?? 0;
};

var myModal = new Modal(document.getElementById('modal_informations'), {
    keyboard: false
});

document.getElementById('show-modal').addEventListener("click", showModal);
document.getElementById('btn-generate').addEventListener("click", goToGenerateDca);