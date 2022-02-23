import React from 'react';
import AuthAPI from "../services/authAPI";
import {Navigate, useLocation} from "react-router-dom";

const RequireAuth = ({children}) => {
    let location = useLocation();
    if (!AuthAPI.isAuthenticated()) {
        return <Navigate to="/login" state={{from: location}} replace/>
    }
    return children;
};

export default RequireAuth;
