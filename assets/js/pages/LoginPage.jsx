import React, {useContext, useState} from 'react';
import AuthAPI from "../services/authAPI";
import {useNavigate} from "react-router-dom";
import AuthContext from "../contexts/AuthContext";
import Field from "../components/Forms/Field";

const LoginPage = () => {
    const {setIsAuthenticated} = useContext(AuthContext);
    const navigate = useNavigate();
    const [credentials, setCredentials] = useState({
        username: "",
        password: ""
    });

    const [errorMsg, setErrorMsg] = useState("");

    const handleChange = ({currentTarget}) => {
        const {value, name} = currentTarget;
        setCredentials({...credentials, [name]: value});
    };

    const handleSubmit = async event => {
        event.preventDefault();
        try {
            await AuthAPI.authenticate(credentials);
            setErrorMsg("");
            setIsAuthenticated(true);
            navigate("/", true);
        } catch (error) {
            setErrorMsg("Aucun compte ne possède cette adresse");
        }
    };

    return (
        <div className="container">
            <h1>Connexion</h1>
            <form onSubmit={handleSubmit}>
                <Field name="username"
                       label="Adresse email"
                       value={credentials.username}
                       onChange={handleChange}
                       placeholder="Adresse email de connexion"
                       error={errorMsg}/>


                <Field name="password"
                       label="Mot de passe"
                       value={credentials.password}
                       onChange={handleChange}
                       placeholder="Mot de passe"
                       type="password"
                       error={errorMsg}/>

                <div className="form-group">
                    <button type="submit" className="btn btn-success">
                        Connexion
                    </button>
                </div>
            </form>
        </div>
    );
};

export default LoginPage;
