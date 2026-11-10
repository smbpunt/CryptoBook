import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
/*
 * Form collection: adds a row from the prototype, removes the row of the clicked button.
 * Rows are <tr>: the prototype is parsed inside a <tbody>, a <template> would drop the table elements otherwise.
 */
export default class extends Controller {
    static targets = ['items'];

    static values = {
        prototype: String,
        index: Number,
    };

    add(event) {
        event.preventDefault();
        const tbody = document.createElement('tbody');
        tbody.innerHTML = this.prototypeValue.replace(/__name__/g, String(this.indexValue));
        this.itemsTarget.append(...tbody.children);
        this.indexValue++;
    }

    remove(event) {
        event.preventDefault();
        event.currentTarget.closest('tr')?.remove();
    }
}
