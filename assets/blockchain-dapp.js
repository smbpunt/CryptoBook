import Choices from "choices.js";

function BlockchainDapp(blockchain_select_id, dapp_select_id, dapp_div_id = 'id-dapp') {
    new Choices('#' + blockchain_select_id);
    new Choices('#' + dapp_select_id);
    const blockchain = document.getElementById(blockchain_select_id);
    const dapp = document.getElementById(dapp_select_id);
    const dappDiv = document.getElementById(dapp_div_id);

    blockchain.addEventListener('change', function () {
        const form = this.closest('form');
        const method = form.method;
        const url = form.action;

        var request = new XMLHttpRequest();
        request.withCredentials = true;
        request.open(method, url, true);
        request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        request.onload = function () {
            if (this.status >= 200 && this.status < 400) {
                //Success
                const html = new DOMParser().parseFromString(this.response, 'text/html');
                dappDiv.innerHTML = html.querySelector('#' + dapp_div_id).innerHTML;
                new Choices('#' + dapp_select_id);
            } else {
                //Error from server
                console.log('Server error');
            }
        };

        request.onerror = function () {
            //Connection error
            console.log('Connection error');
        };
        var formdata = new FormData(form);
        // Je retire le champ dapp, pour pas qu'il soit handle par la Request
        formdata.set(dapp.name, "");
        request.send(formdata);
    });
}

export {BlockchainDapp};