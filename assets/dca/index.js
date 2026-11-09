// Buttons of the "Générer mes positions" and "DCA Auto" dialogs (opening is handled by the dialog controller).
document.getElementById('btn-generate')?.addEventListener('click', (e) => {
    e.preventDefault();
    const value = document.getElementById('dca_value').value || 0;
    window.location.href = e.currentTarget.dataset.path + '/' + value;
});

document.getElementById('btn-generate-dcaauto')?.addEventListener('click', (e) => {
    e.preventDefault();
    if (!e.currentTarget.closest('form').reportValidity()) {
        return;
    }
    const params = new URLSearchParams({
        crypto: document.getElementById('dca_auto_coin').value,
        t: document.getElementById('dca_auto_type_recurr').value,
        nb: document.getElementById('dca_auto_nb_recurr').value,
        total: document.getElementById('dca_auto_value').value,
        d: document.getElementById('dca_auto_date_first').value,
    });
    window.location.href = e.currentTarget.dataset.path + '?' + params;
});
