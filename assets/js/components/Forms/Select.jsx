import React from 'react';

const Select = ({name, value, error = "", label, onChange, children}) => {
    return (
        <div className="form-group mt-4">
            <label className="form-label" htmlFor={name}>{label}</label>
            <select
                onChange={onChange}
                name={name}
                id={name}
                value={value}
                className={"form-control form-select" + (error && " is-invalid")}>
                {children}
            </select>
            <p className="invalid-feedback">{error}</p>
        </div>
    );
};

export default Select;
