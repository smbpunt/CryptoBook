import axios from "axios";

function findAll() {
    return axios.get("http://127.0.0.1:8000/api/type_projects")
        .then(response => response.data["hydra:member"]);
}

function deleteData(id) {
    return axios.delete("http://127.0.0.1:8000/api/type_projects/" + id);
}

function create(data) {
    console.log(data);
    return axios.post("http://127.0.0.1:8000/api/type_projects", data)
        .then(response => console.log(response));
}

async function find(id) {
    return axios.get("http://127.0.0.1:8000/api/type_projects" + "/" + id).then(response => response.data);
}

function update(id, data) {
    return axios.put("http://127.0.0.1:8000/api/type_projects" + "/" + id, data)
        .then(async response => response);
}

export default {
    findAll,
    delete: deleteData,
    create,
    find,
    update
}