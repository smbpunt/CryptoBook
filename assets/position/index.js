// Toggles the per-coin detail row of the positions table.
document.querySelectorAll('.btn-split').forEach((btn) => {
    btn.addEventListener('click', () => {
        const detail = document.getElementById('detail-' + btn.dataset.id);
        const expanded = detail.classList.toggle('hidden') === false;
        btn.setAttribute('aria-expanded', String(expanded));
    });
});
