import React, {useState} from "react";
import ReactDOM from "react-dom";
import {HashRouter as Router, Route, Routes} from "react-router-dom";

// any CSS you import will output into a single css file (app.css in this case)
// start the Stimulus application
import './bootstrap';
import './styles/app.scss';

import NavBar from "./js/components/Navbar";
import HomePage from "./js/pages/HomePage";
import LoginPage from "./js/pages/LoginPage";
import AuthAPI from "./js/services/authAPI";
import RequireAuth from "./js/components/RequireAuth";
import AuthContext from "./js/contexts/AuthContext";
import Cryptocurrencies from "./js/pages/Cryptocurrencies/Cryptocurrencies";
import Cryptocurrency from "./js/pages/Cryptocurrencies/Cryptocurrency";
import Blockchains from "./js/pages/Blockchains/Blockchains";
import Blockchain from "./js/pages/Blockchains/Blockchain";
import Dapps from "./js/pages/Dapp/Dapps";
import Dapp from "./js/pages/Dapp/Dapp";
import Exchanges from "./js/pages/Exchanges/Exchanges";
import Exchange from "./js/pages/Exchanges/Exchange";
import DepositTypes from "./js/pages/DepositType/DepositTypes";
import DepositType from "./js/pages/DepositType/DepositType";
import ProjectTypes from "./js/pages/ProjectType/ProjectTypes";
import ProjectType from "./js/pages/ProjectType/ProjectType";
import Positions from "./js/pages/Position/Positions";
import Position from "./js/pages/Position/Position";
import Farmings from "./js/pages/Farming/Farmings";
import Farming from "./js/pages/Farming/Farming";

AuthAPI.setup();

const App = () => {
    const [isAuthenticated, setIsAuthenticated] = useState(AuthAPI.isAuthenticated);

    return (
        <React.StrictMode>
            <AuthContext.Provider value={{
                isAuthenticated,
                setIsAuthenticated
            }}>
                <Router>
                    <NavBar/>
                    <main className="container-fluid bg-dark pt-5 pb-5">
                        <Routes>
                            <Route path="/login"
                                   element={<LoginPage/>}/>
                            <Route path="/positions" element={<RequireAuth><Positions/></RequireAuth>}/>
                            <Route path="/positions/:id" element={<RequireAuth><Position/></RequireAuth>}/>
                            <Route path="/farmings" element={<RequireAuth><Farmings/></RequireAuth>}/>
                            <Route path="/farmings/:id" element={<RequireAuth><Farming/></RequireAuth>}/>
                            <Route path="/cryptos" element={<RequireAuth><Cryptocurrencies/></RequireAuth>}/>
                            <Route path="/cryptos/:id" element={<RequireAuth><Cryptocurrency/></RequireAuth>}/>
                            <Route path="/blockchains/:id" element={<RequireAuth><Blockchain/></RequireAuth>}/>
                            <Route path="/blockchains" element={<RequireAuth><Blockchains/></RequireAuth>}/>
                            <Route path="/dapps/:id" element={<RequireAuth><Dapp/></RequireAuth>}/>
                            <Route path="/dapps" element={<RequireAuth><Dapps/></RequireAuth>}/>
                            <Route path="/ex" element={<RequireAuth><Exchanges/></RequireAuth>}/>
                            <Route path="/ex/:id" element={<RequireAuth><Exchange/></RequireAuth>}/>
                            <Route path="/deposit_types" element={<RequireAuth><DepositTypes/></RequireAuth>}/>
                            <Route path="/deposit_types/:id" element={<RequireAuth><DepositType/></RequireAuth>}/>
                            <Route path="/project_types" element={<RequireAuth><ProjectTypes/></RequireAuth>}/>
                            <Route path="/project_types/:id" element={<RequireAuth><ProjectType/></RequireAuth>}/>
                            <Route path="/" element={<HomePage/>}/>
                        </Routes>
                    </main>
                </Router>
            </AuthContext.Provider>
        </React.StrictMode>
    )
};


const rootElement = document.querySelector('#app');
ReactDOM.render(<App/>, rootElement);