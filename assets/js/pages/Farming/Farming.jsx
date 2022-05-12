import React, {useEffect, useState} from 'react';
import {Link, useNavigate, useParams} from "react-router-dom";
import FarmingAPI from "../../services/farmingsAPI";
import CryptoCurrencyAPI from "../../services/cryptocurrencyAPI";
import BlockchainAPI from "../../services/blockchainAPI";
import Field from "../../components/Forms/Field";
import Select from "../../components/Forms/Select";
import FieldGroup from "../../components/Forms/FieldGroup";
import Textarea from "../../components/Forms/Textarea";
import {func} from "prop-types";


const Farming = () => {
    let params = useParams();
    let navigate = useNavigate();
    const id = params['id'];

    const [farming, setFarming] = useState({
        coin: "",
        dapp: "",
        apr: 0,
        enteredAt: "2022-01-01",
        nbCoins: 0,
        description: ""
    });

    const [cryptocurrencies, setCryptocurrencies] = useState([]);
    const [dapps, setDapps] = useState([]);
    const [blockchains, setBlockchains] = useState([]);
    const [blockchain, setBlockchain] = useState('');
    const [editing, setEditing] = useState(false);
    const [errors, setErrors] = useState({
        nbCoins: "",
        entryCost: "",
        isOpened: "",
        coin: "",
        description: "",
        openedAt: ""
    });

    // Récupération des cryptos
    const fetchCryptocurrencies = async () => {
        try {
            const data = await CryptoCurrencyAPI.findAll();
            setCryptocurrencies(data);
            if (!farming.coin && id === "new") setFarming({...farming, coin: data[0].id.toString()});
        } catch (error) {
            console.log("Impossible de charger les cryptos.");
        }
    };


    const fetchBlockchains = async () => {
        try {
            const data = await BlockchainAPI.findAll();
            setBlockchains(data);
            if(!farming.dapp && id === "new") {
                setBlockchain(data[0].id);
            }
        } catch (error) {
            console.log("Impossible de charger les dapps.");
        }
    };

    // Récupération d'une position
    const fetchFarming = async id => {
        try {
            const {nbCoins, coin, dapp, apr, enteredAt} = await FarmingAPI.find(id);
            setFarming({nbCoins, coin: coin.id, dapp: dapp.id, apr, enteredAt});
        } catch (error) {
            console.log("Impossible de charger le farming demandée");
        }
    };

    // Récupération de la liste des clients à chaque chargement du composant
    useEffect(() => {
        fetchCryptocurrencies();
        fetchBlockchains();
    }, []);

    // Récupération de la bonne position quand l'identifiant de l'URL change
    useEffect(() => {
        if (id !== "new") {
            setEditing(true);
            fetchFarming(id);
        }
    }, [id]);

    // Gestion des changements des inputs dans le formulaire
    const handleChange = ({currentTarget}) => {
        const {name, value} = currentTarget;
        setFarming({...farming, [name]: value});
    };

    const handleChangeBlockchain = ({currentTarget}) => {
        const {value} = currentTarget;
        setBlockchain(value);
        blockchains.forEach(function (blockchain) {
            if(parseInt(value) === parseInt(blockchain.id)) {
                console.log(blockchain);
                setDapps(blockchain.dapps);
                setFarming({...farming, dapp: blockchain.dapps[0].id.toString()});
            }
        });
    }

    console.log(farming);


    // Gestion de la soumission du formulaire
    const handleSubmit = async event => {
        event.preventDefault();

        try {
            if (editing) {
                await FarmingAPI.update(id, farming);
                console.log("Modification enregistrées.");
            } else {
                await FarmingAPI.create(farming);
                console.log("Ajouté !");
            }
            navigate("/farmings", true);
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
                {editing ? "Édition" : "Ajout"} d'un farming
            </h1>
            <form onSubmit={handleSubmit}>

                <div className="row">
                    <div className="col">
                        <Field name="enteredAt"
                               label="Date"
                               type="date"
                               value={farming.enteredAt}
                               onChange={handleChange}
                               placeholder="ex: 01/01/2022"
                               error={errors.enteredAt}/>
                    </div>
                    <div className="col">
                        <FieldGroup name="apr"
                                    label="APR (%)"
                                    type="number"
                                    spanContent="%"
                                    value={farming.apr}
                                    onChange={handleChange}
                                    placeholder="ex: 21.2"
                                    error={errors.apr}/>
                    </div>
                    <div className="col">
                        <Field name="nbCoins"
                               label="Nombre"
                               type="number"
                               value={farming.nbCoins}
                               onChange={handleChange}
                               placeholder="ex: 0.2"
                               error={errors.nbCoins}/>
                    </div>
                    <div className="col">
                        <Select name="coin"
                                label="Cryptomonnaie"
                                value={farming.coin}
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
                    </div>
                </div>
                <div className="row">
                    <div className="col">

                        <Select name="blockchain"
                                label="Blockchain"
                                value={blockchain}
                                error={errors.dapp}
                                onChange={handleChangeBlockchain}
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

                    </div>
                    <div className="col">
                        <Select name="dapp"
                                label="Dapp"
                                value={farming.dapp}
                                onChange={handleChange}
                        >
                            {
                                dapps.map(
                                    dapp => (
                                        <option key={dapp.id}
                                                value={dapp.id}>
                                            {dapp.libelle}
                                        </option>
                                    )
                                )
                            }
                        </Select>
                    </div>
                </div>

                <div className="row">
                    <Textarea
                        name="description"
                        label="Description"
                        value={farming.description}
                        onChange={handleChange}
                        placeholder="ex: ..."
                        error={errors.description}/>
                </div>
                <div className="col-lg-6"/>


                <div className="form-group mt-3">
                    <button type="submit" className="btn btn-success">Valider</button>
                    <Link to="/farmings" className="btn btn-link">Retour</Link>
                </div>
            </form>
        </div>
    );
};

export default Farming;
