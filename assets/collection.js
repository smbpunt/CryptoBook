/*
import Choices from "choices.js";

const choices = new Choices('.js-select2',);
*/

const createElementFromString = function (str) {
    const element = new DOMParser().parseFromString(str, 'text/html');
    return element.documentElement.querySelector('body').firstChild;
};

const addFormToCollection = (e) => {
    e.preventDefault();
    const wrapper = document.querySelector('.' + e.currentTarget.dataset.wrapper);
    const item = document.createElement('tr');
    item.className = "js-item";

    item.innerHTML = wrapper
        .dataset
        .prototype
        .replace(
            /__name__/g,
            wrapper.dataset.index
        );

    wrapper.appendChild(item);

    wrapper.dataset.index++;
    handleRemoveButtons();
};

const handleRemoveButtons = () => {
    document
        .querySelectorAll('.js-remove')
        .forEach(btn => btn.addEventListener("click", removeFormToCollection));
}

const removeFormToCollection = (e) => {
    e.currentTarget.closest('.js-item').remove();
};


document
    .querySelectorAll('.add_item_link')
    .forEach(btn => btn.addEventListener("click", addFormToCollection));

handleRemoveButtons();