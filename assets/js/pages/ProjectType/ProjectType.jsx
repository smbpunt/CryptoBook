import React, {useEffect, useState} from 'react';
import {Link, useNavigate, useParams} from "react-router-dom";
import API from "../../services/projectTypeAPI";
import Field from "../../components/Forms/Field";

const ProjectType = () => {
    let params = useParams();
    let navigate = useNavigate();
    const id = params['id'];

    const [projectType, setProjectType] = useState({
        libelle: "",
    });
    const [editing, setEditing] = useState(false);
    const [errors, setErrors] = useState({
        libelle: "",
    });


    // Récupération d'une projectType
    const fetchDepositType = async id => {
        try {
            const {libelle} = await API.find(id);
            setProjectType({libelle});
        } catch (error) {
            console.log("Impossible de charger la projectType demandée");
        }
    };

    // Récupération de la bonne projectType quand l'identifiant de l'URL change
    useEffect(() => {
        if (id !== "new") {
            setEditing(true);
            fetchDepositType(id);
        }

    }, [id]);

    // Gestion des changements des inputs dans le formulaire
    const handleChange = ({currentTarget}) => {
        const {name, value} = currentTarget;
        setProjectType({...projectType, [name]: value});
    };

    // Gestion de la soumission du formulaire
    const handleSubmit = async event => {
        event.preventDefault();

        try {
            if (editing) {
                await API.update(id, projectType);
                console.log("La projectType a bien été modifiée");
            } else {
                await API.create(projectType);
                console.log("La projectType a bien été enregistrée");
            }
            navigate("/project_types", true);
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
                {editing ? "Édition" : "Ajout"} d'un type de projet
            </h1>
            <form onSubmit={handleSubmit}>
                <Field name="libelle"
                       label="Nom"
                       value={projectType.libelle}
                       onChange={handleChange}
                       placeholder="ex: DAO"
                       error={errors.libelle}/>

                <div className="form-group mt-3">
                    <button type="submit" className="btn btn-success">Valider</button>
                    <Link to="/deposit_types" className="btn btn-link">Retour</Link>
                </div>
            </form>
        </div>
    );
};

export default ProjectType;
