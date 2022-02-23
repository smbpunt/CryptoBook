import React, {useEffect, useState} from 'react';
import {Link} from "react-router-dom";
import API from "../../services/positionsAPI";
import Pagination from "../../components/Pagination";

const Positions = () => {
    const [currentPage, setCurrentPage] = useState(1);
    const [search, setSearch] = useState("");
    const [lstData, setLstData] = useState([]);

    const fetchAll = async () => {
        try {
            const data = await API.findAll();
            setLstData(data);
        } catch (e) {
            console.log(e.response);
        }
    }

    //Au chargement du composant
    useEffect(() => {
        fetchAll();
    }, []);

    const handleDelete = async id => {
        const initialData = [...lstData];
        //L'approche optimiste (on filtre avant d'avoir la réponse du serveur)
        setLstData(lstData.filter(data => data.id !== id));
        //L'approche pessismiste (On gère le cas ou le serveur nous retourne une erreur
        try {
            await API.delete(id);
        } catch (e) {
            setLstData(initialData);
        }
    }

    const handlePageChange = page => setCurrentPage(page);

    const handleSearch = ({currentTarget}) => {
        setSearch(currentTarget.value);
        setCurrentPage(1);
    }

    const itemsPerPage = 10;

    const filteredDatas = lstData.filter(
        d =>
            (d.coin.symbol && d.coin.symbol.toLowerCase().includes(search.toLowerCase()))
    );

    const paginated = Pagination.getData(
        filteredDatas,
        currentPage,
        itemsPerPage
    );

    return (
        <div className="container">
            <div className="d-flex justify-content-between align-items-center mb-3">
                <h1>Liste des positions</h1>
                <Link to="/positions/new" className="btn btn-primary">Creer</Link>
            </div>
            <div className="form-group p-3">
                <input type="text"
                       onChange={handleSearch}
                       value={search}
                       className="form-control"
                       placeholder="Rechercher..."
                />
            </div>

            <table className="table table-sm table-hover table-bordered table-striped">
                <thead>
                <tr>
                    <th scope="col"/>
                    <th scope="col"/>
                    <th scope="col">Ouvert le</th>
                    <th scope="col">Nombre acheté</th>
                    <th scope="col">Coût d'entrée</th>
                    <th scope="col">Valeur actuelle</th>
                    <th/>
                </tr>
                </thead>
                <tbody>
                {
                    paginated.map(
                        position => (
                            <tr className="align-middle" key={position.id}>
                                <td className="text-center"><img src={position.coin.urlImgThumb} alt={crypto.symbol}/></td>
                                <td className="text-center">{position.coin.symbol.toUpperCase()}</td>
                                <td className="text-end">{new Date(position.openedAt).toLocaleDateString()}</td>
                                <td className="text-end">{position.nbCoins}</td>
                                <td className="text-end">{position.entryCost} $</td>
                                <td className="text-end">{position.coin.priceUsd * position.nbCoins} $</td>
                                <td className="text-end">
                                    <div className="btn-group" role="group">
                                        <Link to={"/deposit_types/" + position.id} className="btn btn-primary btn-sm">Modifier</Link>
                                        <button onClick={() => handleDelete(position.id)}
                                                className="btn btn-sm btn-danger"
                                        >Supprimer
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        )
                    )
                }
                </tbody>
            </table>

            {itemsPerPage < filteredDatas.length && (
                <Pagination
                    currentPage={currentPage}
                    itemsPerPage={itemsPerPage}
                    length={filteredDatas.length}
                    onPageChanged={handlePageChange}
                />
            )}

        </div>
    );
};

export default Positions;
