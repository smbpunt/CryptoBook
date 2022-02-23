import React, {useEffect, useState} from 'react';
import Field from "../../components/Forms/Field";
import {Link, useParams} from "react-router-dom";
import API from "../../services/cryptocurrencyAPI";

const Cryptocurrency = () => {
    let params = useParams();
    const id = params['id'];

    const [data, setData] = useState({
        libelleCoingecko: "",
        color: "#000000",
        isStable: false
    });

    const [errors, setErrors] = useState({
        libelleCoingecko: "",
        color: "",
        isStable: ""
    });

    const [editing, setEditing] = useState(false);

    const fetch = async id => {
        try {
            const {libelleCoingecko, color, isStable} = await API.find(id);
            setData({libelleCoingecko, color, isStable});
        } catch (e) {
            console.log(e.response);
        }
    }

    useEffect(() => {
        if (id !== "new") {
            setEditing(true);
            fetch(id);
        }
    }, []);

    const handleChange = ({currentTarget}) => {
        const {name, value} = currentTarget;
        setData({...data, [name]: value});
    };

    const handleSubmit = async event => {
        event.preventDefault();

        try {
            setErrors({});

            if (editing) {
                await API.update(id, data);
                // toast.success("Le client a bien été modifié");
            } else {
                await API.create(data);
                //  toast.success("Le client a bien été créé");
                //history.replace("/customers");
            }
        } catch ({response}) {
            console.log(response);
            const {violations} = response.data;
            console.log(violations);

            if (violations) {
                const apiErrors = {};
                violations.forEach(({propertyPath, message}) => {
                    apiErrors[propertyPath] = message;
                });

                setErrors(apiErrors);
                //   toast.error("Des erreurs dans votre formulaire !");
            }
        }
    };

    return (
        <div className="container">
            <h1>
                Ajout d'une crypto
            </h1>
            <form onSubmit={handleSubmit}>
                <Field name="libelleCoingecko"
                       label="ID API Coingecko"
                       value={data.libelleCoingecko}
                       onChange={handleChange}
                       placeholder="ex: bitcoin"
                       error={errors.libelleCoingecko}/>
                <Field name="color"
                       label="Couleur"
                       type="color"
                       value={data.color}
                       onChange={handleChange}
                       placeholder="ex: bitcoin"
                       error={errors.color}/>
                {/*                <Field name="isStable"
                       label="isStable"
                       value={data.isStable}
                       onChange={handleChange}
                       placeholder="ex: bitcoin"
                       error={errors.isStable}/>*/}

                <div className="form-group mt-3">
                    <button type="submit" className="btn btn-success">Valider</button>
                    <Link to="/cryptos" className="btn btn-link">Retour</Link>
                </div>
            </form>
        </div>
    );
};

export default Cryptocurrency;
