console.log('farming/form.js loaded');
import Choices from "choices.js";

new Choices('#strategy_farming_coin');
new Choices('#strategy_farming_blockchain');
new Choices('#strategy_farming_dapp');

const blockchain = document.getElementById('strategy_farming_blockchain');
const dapp = document.getElementById('strategy_farming_dapp');
const dappDiv = document.getElementById('id-dapp');

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
            dappDiv.innerHTML = html.querySelector('#id-dapp').innerHTML;
            new Choices('#strategy_farming_dapp');
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