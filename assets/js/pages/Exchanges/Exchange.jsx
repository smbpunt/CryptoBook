import React, {useEffect, useState} from 'react';
import {Link, useParams} from "react-router-dom";
import API from "../../services/exchangeAPI";
import Field from "../../components/Forms/Field";

const Blockchain = () => {
    let params = useParams();
    const id = params['id'];

    const [exchange, setExchange] = useState({
        libelle: "",
        url: "",
    });
    const [editing, setEditing] = useState(false);
    const [errors, setErrors] = useState({
        libelle: "",
        url: "",
    });


    // Récupération d'une exchange
    const fetchExchange = async id => {
        try {
            const {libelle, url} = await API.find(id);
            setExchange({libelle, url});
        } catch (error) {
            console.log("Impossible de charger la exchange demandée");
        }
    };

    // Récupération de la bonne exchange quand l'identifiant de l'URL change
    useEffect(() => {
        if (id !== "new") {
            setEditing(true);
            fetchExchange(id);
        }

    }, [id]);

    // Gestion des changements des inputs dans le formulaire
    const handleChange = ({currentTarget}) => {
        const {name, value} = currentTarget;
        setExchange({...exchange, [name]: value});
    };

    // Gestion de la soumission du formulaire
    const handleSubmit = async event => {
        event.preventDefault();

        try {
            if (editing) {
                await API.update(id, exchange);
                console.log("La exchange a bien été modifiée");
            } else {
                await API.create(exchange);
                console.log("La exchange a bien été enregistrée");
            }
        } catch ({response}) {
            const {violations} = response.data;
            if (violations) {
                const apiErrors = {};
                violations.forEach(({propertyPath, message}) => {
                    apiErrors[propertyPath] = message;
                });
                setErrors(apiErrors);
                console.log("Des erreurs dans votre formulaire");
            }
        }
    };

    return (
        <div className="container">
            <h1>
                {editing ? "Édition" : "Ajout"} d'un exchange
            </h1>
            <form onSubmit={handleSubmit}>
                <Field name="libelle"
                       label="Nom"
                       value={exchange.libelle}
                       onChange={handleChange}
                       placeholder="ex: bitcoin"
                       error={errors.libelle}/>

                <Field name="url"
                       label="URL"
                       value={exchange.url}
                       onChange={handleChange}
                       placeholder="ex: https://..."
                       error={errors.url}/>

                <div className="form-group mt-3">
                    <button type="submit" className="btn btn-success">Valider</button>
                    <Link to="/ex" className="btn btn-link">Retour</Link>
                </div>
            </form>
        </div>
    );
};

export default Blockchain;
