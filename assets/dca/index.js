import {Modal} from 'bootstrap';

console.log("index dca.");

const modalDca = new Modal(document.getElementById('modal_informations'), {
    keyboard: false
});


const modalDcaAuto = new Modal(document.getElementById('modal_dca_auto'), {
    keyboard: false
});

const showModal = function (modal) {
    modal.show();
};

const goToGenerateDca = (e) => {
    e.preventDefault();
    const path = e.currentTarget.dataset.path;
    const valueDca = document.getElementById('dca_value').value;
    window.location.href = path + '/' + valueDca ?? 0;
};

const goToDcaauto = (e) => {
    e.preventDefault();
    const path = e.currentTarget.dataset.path;

    const value = document.getElementById('dca_auto_value').value;
    const type_recurr = document.getElementById('dca_auto_type_recurr').value;
    const nb_recurr = document.getElementById('dca_auto_nb_recurr').value;
    const date_first = document.getElementById('dca_auto_date_first').value;
    const coin = document.getElementById('dca_auto_coin').value;

    window.location.href = path + '?crypto=' + coin + '&t=' + type_recurr + '&nb=' + nb_recurr + '&total=' + value + '&d=' + date_first;
}

document.getElementById('show-modal').addEventListener("click", function (e) {
    e.preventDefault();
    showModal(modalDca);
});

document.getElementById('show-modal-dcaauto').addEventListener("click", function (e) {
    e.preventDefault();
    showModal(modalDcaAuto);
});

document.getElementById('btn-generate').addEventListener("click", goToGenerateDca);
document.getElementById('btn-generate-dcaauto').addEventListener("click", goToDcaauto);


