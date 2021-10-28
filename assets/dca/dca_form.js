console.log('dca_form.js loaded');

const addTagFormDeleteLink = (venteFormLi) => {
    const removeFormButton = document.createElement('button')
    removeFormButton.classList
    removeFormButton.innerText = 'Delete this part'

    venteFormLi.append(removeFormButton);

    removeFormButton.addEventListener('click', (e) => {
        e.preventDefault()
        // remove the li for the vente form
        venteFormLi.remove();
    });
}

const tags = document.querySelectorAll('ul.parts.li')
tags.forEach((tag) => {
    addTagFormDeleteLink(tag)
})

const addFormToCollection = (e) => {
    const collectionHolder = document.querySelector('.' + e.currentTarget.dataset.collectionHolderClass);
    const item = document.createElement('li');

    item.innerHTML = collectionHolder
        .dataset
        .prototype
        .replace(
            /__name__/g,
            collectionHolder.dataset.index
        );

    collectionHolder.appendChild(item);

    collectionHolder.dataset.index++;
    addTagFormDeleteLink(item);
};

document
    .querySelectorAll('.add_item_link')
    .forEach(btn => btn.addEventListener("click", addFormToCollection));

/*

document.getElementById('btn-add-vente').addEventListener("click", function () {
    //Je récupère tout ce qu'il me faut dans le html
    const list = document.getElementById(this.dataset.listselector); // la liste
    let counter = list.dataset.widgetcounter; // le compteur
    const widgettags = list.dataset.widgettags; // le html qui va entourer mon prototype
    let prototype = list.dataset.prototype; // le prototype

    prototype = prototype.replace(/__name__/g, counter); // On change le __name__ par l'id de la vente
    counter++; // On incrémente le compteur
    list.dataset.widgetcounter = counter; // On incrémente le compteur sur le html

    let outerhtml = document.createElement(widgettags); // Je crée mon élement qui entoure le prototype
    outerhtml.innerHTML = prototype; // On ajoute le prototype
    list.insertAdjacentElement('beforeend', outerhtml); // On l'ajoute à la fin de la liste
});*/
