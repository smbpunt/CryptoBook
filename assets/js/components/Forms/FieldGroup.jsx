import React from 'react';

const FieldGroup = ({name, label, value, onChange, placeholder, spanContent = "$", type = "text", error = ""}) => (
    <div className="form-group mt-4">
        <label className="form-label" htmlFor={name}>{label}</label>

        <div className="input-group">
            <input
                value={value}
                onChange={onChange}
                type={type}
                placeholder={placeholder}
                name={name}
                id={name}
                className={"form-control" + (error && " is-invalid")}/>
            <span className="input-group-text">{spanContent}</span>
            {error && <p className="invalid-feedback">{error}</p>}
        </div>
    </div>


);

export default FieldGroup;
