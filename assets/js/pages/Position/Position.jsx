import React, {useEffect, useState} from 'react';
import {Link, useNavigate, useParams} from "react-router-dom";
import PositionAPI from "../../services/positionsAPI";
import CryptoCurrencyAPI from "../../services/cryptocurrencyAPI";
import Field from "../../components/Forms/Field";
import Select from "../../components/Forms/Select";
import FieldGroup from "../../components/Forms/FieldGroup";
import Textarea from "../../components/Forms/Textarea";


const Position = () => {
    let params = useParams();
    let navigate = useNavigate();
    const id = params['id'];

    const [position, setPosition] = useState({
        nbCoins: "",
        entryCost: 0,
        isOpened: true,
        coin: "",
        description: "",
        openedAt: "2022-01-01"
    });
    const [cryptocurrencies, setCryptocurrencies] = useState([]);
    const [editing, setEditing] = useState(false);
    const [errors, setErrors] = useState({
        nbCoins: "",
        entryCost: "",
        isOpened: "",
        coin: "",
        description: "",
        openedAt: ""
    });

    // Récupération des clients
    const fetchCryptocurrencies = async () => {
        try {
            const data = await CryptoCurrencyAPI.findAll();
            setCryptocurrencies(data);

            if (!position.coin && id === "new") setPosition({...position, coin: data[0].id});
        } catch (error) {
            console.log("Impossible de charger les cryptos.");
        }
    };

    // Récupération d'une position
    const fetchPosition = async id => {
        try {
            const {nbCoins, entryCost, isOpened, coin} = await PositionAPI.find(id);
            setPosition({nbCoins, entryCost, isOpened, coin: coin.id});
        } catch (error) {
            console.log("Impossible de charger la position demandée");
        }
    };

    // Récupération de la liste des clients à chaque chargement du composant
    useEffect(() => {
        fetchCryptocurrencies();
    }, []);

    // Récupération de la bonne position quand l'identifiant de l'URL change
    useEffect(() => {
        if (id !== "new") {
            setEditing(true);
            fetchPosition(id);
        }

    }, [id]);

    // Gestion des changements des inputs dans le formulaire
    const handleChange = ({currentTarget}) => {
        const {name, value} = currentTarget;
        setPosition({...position, [name]: value});
    };

    // Gestion de la soumission du formulaire
    const handleSubmit = async event => {
        event.preventDefault();

        try {
            if (editing) {
                await PositionAPI.update(id, position);
                console.log("La position a bien été modifiée");
            } else {
                await PositionAPI.create(position);
                console.log("La position a bien été enregistrée");
            }
            navigate("/positions", true);
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
                {editing ? "Édition" : "Ajout"} d'une position
            </h1>
            <form onSubmit={handleSubmit}>

                <div className="row">
                    <div className="col-lg-8">
                        <div className="row">
                            <div className="col">
                                <Field name="openedAt"
                                       label="Date"
                                       type="date"
                                       value={position.openedAt}
                                       onChange={handleChange}
                                       placeholder="ex: 01/01/2022"
                                       error={errors.openedAt}/>
                            </div>
                            <div className="col">
                                <Select name="coin"
                                        label="Cryptomonnaie"
                                        value={position.coin}
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
                                <Field name="nbCoins"
                                       label="Nombre"
                                       type="number"
                                       value={position.nbCoins}
                                       onChange={handleChange}
                                       placeholder="ex: 0.1"
                                       error={errors.nbCoins}/>
                            </div>
                            <div className="col">
                                <FieldGroup name="entryCost"
                                            label="Cout"
                                            type="number"
                                            value={position.entryCost}
                                            onChange={handleChange}
                                            placeholder="ex: 1000"
                                            error={errors.entryCost}/>
                            </div>
                        </div>

                        <div className="row">
                            <Textarea
                                name="description"
                                label="Description"
                                value={position.description}
                                onChange={handleChange}
                                placeholder="ex: ..."
                                error={errors.description}/>
                        </div>
                    </div>
                    <div className="col-lg-6"/>
                </div>


                <div className="form-group mt-3">
                    <button type="submit" className="btn btn-success">Valider</button>
                    <Link to="/positions" className="btn btn-link">Retour</Link>
                </div>
            </form>
        </div>
    );
};

export default Position;
