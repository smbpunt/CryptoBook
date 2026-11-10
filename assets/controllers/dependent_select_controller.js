import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
/*
 * Reloads a field whose choices depend on another one (dapp ← blockchain). Set on the <form>.
 * Posts the form without the dependent field, then swaps the field with the one of the response.
 * The controller sees `X-Requested-With` (Request::isXmlHttpRequest()) and does not save the entity.
 */
export default class extends Controller {
    static targets = ['field'];

    #abort = null;

    disconnect() {
        this.#abort?.abort();
    }

    async refresh() {
        // A newer choice wins: a slower response for the previous one must not overwrite it.
        this.#abort?.abort();
        const abort = new AbortController();
        this.#abort = abort;

        const data = new FormData(this.element);
        this.fieldTarget.querySelectorAll('[name]').forEach((field) => data.set(field.name, ''));

        try {
            const response = await fetch(this.element.action, {
                method: this.element.method,
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: abort.signal,
            });
            // The form is invalid without the dependent field: a 422 still carries the new choices.
            if (!response.ok && response.status !== 422) {
                throw new Error(`HTTP ${response.status}`);
            }
            const html = new DOMParser().parseFromString(await response.text(), 'text/html');
            const field = html.getElementById(this.fieldTarget.id);
            if (field && !abort.signal.aborted) {
                this.fieldTarget.innerHTML = field.innerHTML;
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Dependent field not reloaded', error);
            }
        }
    }
}
