import React, {useEffect, useState} from 'react';
import {Link, useParams} from "react-router-dom";
import BlockchainAPI from "../../services/blockchainAPI";
import DappAPI from "../../services/dappAPI";
import Field from "../../components/Forms/Field";
import Select from "../../components/Forms/Select";

const Dapp = () => {
    let params = useParams();
    const id = params['id'];

    const [dapp, setDapp] = useState({
        libelle: "",
        url: "",
        blockchain: "",
    });
    const [blockchains, setBlockchains] = useState([]);
    const [editing, setEditing] = useState(false);
    const [errors, setErrors] = useState({
        libelle: "",
        url: "",
        blockchain: "",
    });

    // Récupération des clients
    const fetchBlockchains = async () => {
        try {
            const data = await BlockchainAPI.findAll();
            setBlockchains(data);

            if (!dapp.blockchain && id === "new") setDapp({...dapp, blockchain: data[0].id});
        } catch (error) {
            console.log("Impossible de charger les blockchains");
        }
    };

    // Récupération d'une dapp
    const fetchDapp = async id => {
        try {
            const {libelle, url, blockchain} = await DappAPI.find(id);
            setDapp({libelle, url, blockchain: blockchain.id});
        } catch (error) {
            console.log("Impossible de charger la dapp demandée");
        }
    };

    // Récupération de la liste des clients à chaque chargement du composant
    useEffect(() => {
        fetchBlockchains();
    }, []);

    // Récupération de la bonne blockchain quand l'identifiant de l'URL change
    useEffect(() => {
        if (id !== "new") {
            setEditing(true);
            fetchDapp(id);
        }
    }, [id]);

    // Gestion des changements des inputs dans le formulaire
    const handleChange = ({currentTarget}) => {
        const {name, value} = currentTarget;
        setDapp({...dapp, [name]: value});
    };

    // Gestion de la soumission du formulaire
    const handleSubmit = async event => {
        event.preventDefault();

        try {
            if (editing) {
                await DappAPI.update(id, dapp);
                console.log("La dapp a bien été modifiée");
            } else {
                await DappAPI.create(dapp);
                console.log("La dapp a bien été enregistrée");
            }
        } catch ({response}) {
            console.log(response);
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
                Ajout d'une dapp
            </h1>
            <form onSubmit={handleSubmit}>
                <Field name="libelle"
                       label="Nom dapp"
                       value={dapp.libelle}
                       onChange={handleChange}
                       placeholder="ex: bitcoin"
                       error={errors.libelle}/>
                <Field name="url"
                       label="URL"
                       value={dapp.url}
                       onChange={handleChange}
                       placeholder="ex: https://..."
                       error={errors.url}/>
                <Select name="blockchain"
                        label="Cryptomonnaie"
                        value={dapp.blockchain}
                        error={errors.blockchain}
                        onChange={handleChange}
                >
                    {
                        blockchains.map(
                            blockchain => (
                                <option key={blockchain.id}
                                        value={blockchain.id}>
                                    {blockchain.libelle}
                                </option>
                            )
                        )
                    }
                </Select>

                <div className="form-group mt-3">
                    <button type="submit" className="btn btn-success">Valider</button>
                    <Link to="/dapps" className="btn btn-link">Retour</Link>
                </div>
            </form>
        </div>
    );
};

export default Dapp;
