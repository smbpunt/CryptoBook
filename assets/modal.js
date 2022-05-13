import {Modal} from 'bootstrap';

console.log("Chargement des modal boostrap ok.");


const handleButtons = (e) => {
    //const id = e.currentTarget.dataset.id; //id
    const titlemodal = e.currentTarget.dataset.titlemodal;
    myModal.show();

    const url = e.currentTarget.dataset.url;
    var request = new XMLHttpRequest();
    request.withCredentials = true;
    request.open('POST', url, true);
    request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    var body = document.getElementById('modal-body-id');
    var title = document.getElementById('modal-title-id');
    request.onload = function () {
        if (this.status >= 200 && this.status < 400) {
            //Success
            body.innerHTML = this.response;
            title.innerHTML = titlemodal;
        } else {
            //Error from server
            console.log('Server error');
            body.innerHTML = "Erreur de connexion au serveur. Veuillez ré-essayer.";
            title.innerHTML = "Erreur";
        }
    };

    request.onerror = function () {
        //Connection error
        console.log('Connection error');
        body.innerHTML = "Erreur de connexion au serveur. Veuillez ré-essayer.";
        title.innerHTML = "Erreur";
    };

    request.send();
};

var myModal = new Modal(document.getElementById('modal_informations'), {
    keyboard: false
});

document
    .querySelectorAll('.button-infos')
    .forEach(btn => btn.addEventListener("click", handleButtons));
