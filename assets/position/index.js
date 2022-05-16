console.log('position index.js ok.');

const handleButtons = (e) => {
    const btn = e.currentTarget;
    const id = btn.dataset.id;

    const tdDetail = document.getElementById('detail-' + id);

    if (tdDetail.classList.contains('d-none')) {
        tdDetail.classList.remove('d-none');
        e.currentTarget.innerHTML = '<i class="bi bi-arrows-collapse"></i>';
    } else {
        tdDetail.classList.add('d-none');
        e.currentTarget.innerHTML = '<i class="bi bi-arrows-expand"></i>';
    }

};

document
    .querySelectorAll('.btn-split')
    .forEach(btn => btn.addEventListener("click", handleButtons));
