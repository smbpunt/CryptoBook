import {Tab} from 'bootstrap';

const triggerTabList = [].slice.call(document.querySelectorAll('#myTab a'));
triggerTabList.forEach(function (triggerEl) {
    const tabTrigger = new Tab(triggerEl)
    triggerEl.addEventListener('click', function (event) {
        event.preventDefault()
        tabTrigger.show()
    })
});

console.log("Chargement des Tab boostrap ok.");