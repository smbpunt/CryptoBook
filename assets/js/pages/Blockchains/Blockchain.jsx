import React, {useEffect, useState} from 'react';
import {Link, useParams} from "react-router-dom";
import BlockchainAPI from "../../services/blockchainAPI";
import CryptoCurrencyAPI from "../../services/cryptocurrencyAPI";
import Field from "../../components/Forms/Field";
import Select from "../../components/Forms/Select";

const Blockchain = () => {
    let params = useParams();
    const id = params['id'];

    const [blockchain, setBlockchain] = useState({
        libelle: "",
        coin: "",
    });
    const [cryptocurrencies, setCryptocurrencies] = useState([]);
    const [editing, setEditing] = useState(false);
    const [errors, setErrors] = useState({
        libelle: "",
        coin: "",
    });

    // Récupération des clients
    const fetchCryptocurrencies = async () => {
        try {
            const data = await CryptoCurrencyAPI.findAll();
            setCryptocurrencies(data);

            if (!blockchain.coin && id === "new") setBlockchain({...blockchain, coin: data[0].id});
        } catch (error) {
            console.log("Impossible de charger les clients");
        }
    };

    // Récupération d'une blockchain
    const fetchBlockchain = async id => {
        try {
            const {libelle, coin} = await BlockchainAPI.find(id);
            setBlockchain({libelle, coin: coin.id});
        } catch (error) {
            console.log("Impossible de charger la blockchain demandée");
        }
    };

    // Récupération de la liste des clients à chaque chargement du composant
    useEffect(() => {
        fetchCryptocurrencies();
    }, []);

    // Récupération de la bonne blockchain quand l'identifiant de l'URL change
    useEffect(() => {
        if (id !== "new") {
            setEditing(true);
            fetchBlockchain(id);
        }

    }, [id]);

    // Gestion des changements des inputs dans le formulaire
    const handleChange = ({currentTarget}) => {
        const {name, value} = currentTarget;
        setBlockchain({...blockchain, [name]: value});
    };

    // Gestion de la soumission du formulaire
    const handleSubmit = async event => {
        event.preventDefault();

        try {
            if (editing) {
                await BlockchainAPI.update(id, blockchain);
                console.log("La blockchain a bien été modifiée");
            } else {
                await BlockchainAPI.create(blockchain);
                console.log("La blockchain a bien été enregistrée");
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
                Ajout d'une blockchain
            </h1>
            <form onSubmit={handleSubmit}>
                <Field name="libelle"
                       label="Nom blockchain"
                       value={blockchain.libelle}
                       onChange={handleChange}
                       placeholder="ex: bitcoin"
                       error={errors.libelle}/>
                <Select name="coin"
                        label="Cryptomonnaie"
                        value={blockchain.coin}
                        error={errors.coin}
                        onChange={handleChange}
                >
                    {
                        cryptocurrencies.map(
                            cryptocurrency => (
                                <option key={cryptocurrency.id}
                                        value={cryptocurrency.id}>
                                    {cryptocurrency.libelle}
                                </option>
                            )
                        )
                    }
                </Select>

                <div className="form-group mt-3">
                    <button type="submit" className="btn btn-success">Valider</button>
                    <Link to="/blockchains" className="btn btn-link">Retour</Link>
                </div>
            </form>
        </div>
    );
};

export default Blockchain;
