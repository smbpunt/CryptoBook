import axios from "axios";

function findAll() {
    return axios.get("http://127.0.0.1:8000/api/cryptocurrencies")
        .then(response => response.data["hydra:member"]);
}

function deleteCryptocurrency(id) {
    return axios.delete("http://127.0.0.1:8000/api/cryptocurrencies/" + id);
}

function create(cryptocurrency) {
    console.log(cryptocurrency);
    return axios.post("http://127.0.0.1:8000/api/cryptocurrencies", cryptocurrency)
        .then(response => console.log(response));
}

async function find(id) {
    return axios.get("http://127.0.0.1:8000/api/cryptocurrencies" + "/" + id).then(response => response.data);
}

function update(id, cryptocurrency) {
    return axios.put("http://127.0.0.1:8000/api/cryptocurrencies" + "/" + id, cryptocurrency).then(async response => response);
}

export default {
    findAll,
    delete: deleteCryptocurrency,
    create,
    find,
    update
}