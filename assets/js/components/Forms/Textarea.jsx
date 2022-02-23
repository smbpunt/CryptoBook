import React from 'react';

const Field = ({name, label, value, onChange, placeholder, rows = 3, error = ""}) => (
    <div className="form-group mt-4">


        <label className="form-label" htmlFor={name}>{label}</label>
        <textarea
            value={value}
            onChange={onChange}
            placeholder={placeholder}
            name={name}
            className={"form-control" + (error && " is-invalid")}
            id={name} rows={rows}/>
        {error && <p className="invalid-feedback">{error}</p>}
    </div>

);

export default Field;
