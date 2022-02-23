import React, {useEffect, useState} from 'react';
import {Link} from "react-router-dom";
import API from "../../services/cryptocurrencyAPI";
import Pagination from "../../components/Pagination";

const Cryptocurrencies = () => {
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
            console.log(e.response);
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
            (d.libelle && d.libelle.toLowerCase().includes(search.toLowerCase())) ||
            (d.symbol && d.symbol.toLowerCase().includes(search.toLowerCase()))
    );

    const paginated = Pagination.getData(
        filteredDatas,
        currentPage,
        itemsPerPage
    );

    return (
        <div className="container">
            <div className="d-flex justify-content-between align-items-center mb-3">
                <h1>Mes positions</h1>
                <Link to="/cryptos/new" className="btn btn-primary">Creer</Link>
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
                    <th scope="col" className="col-1"/>
                    <th scope="col" className="text-center col-1">Symbol</th>
                    <th scope="col">Libelle</th>
                    <th scope="col" className="text-end col-2">Price USD</th>
                    <th scope="col" className="text-end">Market Cap USD</th>
                    <th scope="col" className="col-1"/>
                </tr>
                </thead>
                <tbody>
                {
                    paginated.map(
                        crypto => (
                            <tr key={crypto.id}>
                                <td className="text-center align-middle"><img src={crypto.urlImgThumb} alt={crypto.symbol}/></td>
                                <td className="text-center" style={{color: crypto.color}}>{crypto.symbol.toUpperCase()}</td>
                                <td>{crypto.libelle}</td>
                                <td className="text-end">{crypto.priceUsd.toLocaleString()} $</td>
                                <td className="text-end">{crypto.mcapUsd.toLocaleString()} $</td>
                                <td className="text-end">
                                    <div className="btn-group" role="group">
                                        <Link to={"/cryptos/" + crypto.id} className="btn btn-primary btn-sm">Modifier</Link>
                                        <button onClick={() => handleDelete(crypto.id)}
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

export default Cryptocurrencies;
