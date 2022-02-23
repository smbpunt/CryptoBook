import axios from "axios";
import jwtDecode from "jwt-decode";

function authenticate(credentials) {
    return axios
        .post("http://127.0.0.1:8000/api/login_check", credentials)
        .then(response => response.data.token)
        .then(token => {
            // Je stocke le token dans mon localStorage
            window.localStorage.setItem("authToken", token);
            // On prévient Axios qu'on a un header par defaut sur toutes nos futures requetes HTTP
            setAxiosToken(token);
            return true;
        });
}

function setAxiosToken(token) {
    axios.defaults.headers["Authorization"] = "Bearer " + token;
}

function logout() {
    window.localStorage.removeItem("authToken");
    delete axios.defaults.headers["Authorization"];
}

function setup() {
    const token = window.localStorage.getItem("authToken");
    if (token) {
        const {exp: expiration} = jwtDecode(token);
        if (expiration * 1000 > new Date().getTime()) {
            setAxiosToken(token);
        }
    }
}

function isAuthenticated() {
    const token = window.localStorage.getItem("authToken");
    if (token) {
        const {exp: expiration} = jwtDecode(token);
        return expiration * 1000 > new Date().getTime();

    }
    return false;
}

function isAdmin() {
    const token = window.localStorage.getItem("authToken");
    if (token) {
        const {roles} = jwtDecode(token);
        return roles.includes('ROLE_ADMIN');
    }
    return false;
}

export default {
    authenticate,
    logout,
    setup,
    isAuthenticated
}