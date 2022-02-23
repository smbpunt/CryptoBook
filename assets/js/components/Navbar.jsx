import React, {useContext} from 'react';
import AuthAPI from "../services/authAPI";
import {NavLink, useNavigate} from "react-router-dom";
import AuthContext from "../contexts/AuthContext";

const NavBar = () => {
    const {isAuthenticated, setIsAuthenticated} = useContext(AuthContext);
    const navigate = useNavigate();
    const handleLogout = () => {
        AuthAPI.logout();
        setIsAuthenticated(false);
        navigate("/login", true);
    }


    return (
        <nav className="navbar navbar-expand-lg navbar-dark bg-dark">
            <div className="container">
                <NavLink className="navbar-brand" to="/">Cryptobook</NavLink>
                <button className="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor02"
                        aria-controls="navbarColor02" aria-expanded="false" aria-label="Toggle navigation">
                    <span className="navbar-toggler-icon"/>
                </button>

                <div className="collapse navbar-collapse" id="navbarColor02">
                    <ul className="navbar-nav me-auto">
                        <li className="nav-item">
                            <NavLink className="nav-link" to="/positions">Positions</NavLink>
                        </li>
                        <li className="nav-item">
                            <NavLink className="nav-link" to="/cryptos">Cryptos</NavLink>
                        </li>
                        <li className="nav-item">
                            <NavLink className="nav-link" to="/blockchains">BCs</NavLink>
                        </li>
                        <li className="nav-item">
                            <NavLink className="nav-link" to="/dapps">Dapps</NavLink>
                        </li>
                        <li className="nav-item">
                            <NavLink className="nav-link" to="/ex">Exchanges</NavLink>
                        </li>
                        <li className="nav-item">
                            <NavLink className="nav-link" to="/deposit_types">Deposit Types</NavLink>
                        </li>
                        <li className="nav-item">
                            <NavLink className="nav-link" to="/project_types">Project Types</NavLink>
                        </li>
                    </ul>
                    <ul className="navbar-nav ml-auto">
                        {(isAuthenticated && (
                            <li>
                                <button onClick={handleLogout} className="btn btn-danger btn-sm">
                                    Déconnexion
                                </button>
                            </li>
                        )) || (
                            <>
                                <li>
                                    <a href="#" className="btn btn-light btn-sm">
                                        Inscription
                                    </a>
                                </li>
                                <li>
                                    <NavLink to="/login" className="btn btn-success btn-sm">
                                        Connexion
                                    </NavLink>
                                </li>
                            </>
                        )
                        }


                    </ul>
                </div>
            </div>
        </nav>
    );
};

export default NavBar;
