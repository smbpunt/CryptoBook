import { Controller } from '@hotwired/stimulus';
import Choices from 'choices.js';

/* stimulusFetch: 'lazy' */
/*
 * Searchable select (Choices.js), added by the form theme to the selects with the `js-select2` class.
 * Choices moves the <select> into its own wrapper: Stimulus sees a disconnect then a connect of the same element,
 * which must not destroy and rebuild it (endless loop). Only a select really taken out of the page is destroyed.
 * Also torn down before Turbo caches the page: the snapshot keeps the plain <select>, enhanced again on restore.
 */
export default class extends Controller {
    #choices = null;
    // Set once the page is being cached: unwrapping the select reconnects it, it must stay plain.
    #cached = false;
    #beforeCache = () => {
        this.#cached = true;
        this.#destroy();
    };

    connect() {
        document.addEventListener('turbo:before-cache', this.#beforeCache);
        if (this.#choices === null && !this.#cached) {
            this.#choices = new Choices(this.element);
        }
    }

    disconnect() {
        document.removeEventListener('turbo:before-cache', this.#beforeCache);
        if (!this.element.isConnected) {
            this.#destroy();
        }
    }

    #destroy() {
        this.#choices?.destroy();
        this.#choices = null;
    }
}
