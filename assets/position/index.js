console.log('position index.js ok.');

const handleButtons = (e) => {
    let tr = e.currentTarget.closest('tr');
    const id = e.currentTarget.dataset.id;
    if (tr.classList.contains('shown')) {
        let toDelete = document.getElementById('info' + id);
        toDelete.remove();
        e.currentTarget.innerHTML = '+';
        tr.classList.remove('shown');
    } else {
        const url = e.currentTarget.dataset.ajax;
        var request = new XMLHttpRequest();
        request.withCredentials = true;
        request.open('POST', url, true);
        request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        request.onload = function () {
            if (this.status >= 200 && this.status < 400) {
                //Success
                console.log(this.response);
                const trChild = document.createElement('tr');
                trChild.id = 'info' + id;
                const tdChild = document.createElement('td');
                tdChild.colSpan = 9;
                tdChild.innerHTML = this.response;
                trChild.appendChild(tdChild);
                tr.after(trChild);
                tr.classList.add('shown');
            } else {
                //Error from server
                console.log('Server error');
            }
        };

        request.onerror = function () {
            //Connection error
            console.log('Connection error');
        };

        request.send();

        e.currentTarget.innerHTML = '-';
    }

};

document
    .querySelectorAll('.infos_pos')
    .forEach(btn => btn.addEventListener("click", handleButtons));
