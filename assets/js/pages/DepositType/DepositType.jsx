import React, {useEffect, useState} from 'react';
import {Link, useNavigate, useParams} from "react-router-dom";
import API from "../../services/depositTypeAPI";
import Field from "../../components/Forms/Field";

const DepositType = () => {
    let params = useParams();
    let navigate = useNavigate();
    const id = params['id'];

    const [depositType, setDepositType] = useState({
        libelle: "",
    });
    const [editing, setEditing] = useState(false);
    const [errors, setErrors] = useState({
        libelle: "",
    });


    // Récupération d'une depositType
    const fetchDepositType = async id => {
        try {
            const {libelle} = await API.find(id);
            setDepositType({libelle});
        } catch (error) {
            console.log("Impossible de charger la depositType demandée");
        }
    };

    // Récupération de la bonne depositType quand l'identifiant de l'URL change
    useEffect(() => {
        if (id !== "new") {
            setEditing(true);
            fetchDepositType(id);
        }

    }, [id]);

    // Gestion des changements des inputs dans le formulaire
    const handleChange = ({currentTarget}) => {
        const {name, value} = currentTarget;
        setDepositType({...depositType, [name]: value});
    };

    // Gestion de la soumission du formulaire
    const handleSubmit = async event => {
        event.preventDefault();

        try {
            if (editing) {
                await API.update(id, depositType);
                console.log("La depositType a bien été modifiée");
            } else {
                await API.create(depositType);
                console.log("La depositType a bien été enregistrée");
            }
            navigate("/deposit_types", true);
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
                {editing ? "Édition" : "Ajout"} d'un type de dépôt
            </h1>
            <form onSubmit={handleSubmit}>
                <Field name="libelle"
                       label="Nom"
                       value={depositType.libelle}
                       onChange={handleChange}
                       placeholder="ex: Carte bleu"
                       error={errors.libelle}/>

                <div className="form-group mt-3">
                    <button type="submit" className="btn btn-success">Valider</button>
                    <Link to="/deposit_types" className="btn btn-link">Retour</Link>
                </div>
            </form>
        </div>
    );
};

export default DepositType;
