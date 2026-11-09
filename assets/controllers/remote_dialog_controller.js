import { Controller } from '@hotwired/stimulus';

/*
 * Loads a server-rendered HTML fragment into a dialog (see App:InfoDialog / App:InfoButton).
 * Paired with the toolkit "dialog" controller, which opens and closes the <dialog>.
 */
export default class extends Controller {
    static targets = ['title', 'body', 'loading'];

    #abort = null;

    disconnect() {
        this.#abort?.abort();
    }

    async load({ params: { url, title } }) {
        // A slower previous response must not overwrite the one asked for last.
        this.#abort?.abort();
        const abort = (this.#abort = new AbortController());

        this.titleTarget.textContent = title || 'Informations';
        this.bodyTarget.innerHTML = this.loadingTarget.innerHTML;

        try {
            const response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: abort.signal,
            });
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            const html = await response.text();
            if (!abort.signal.aborted) {
                this.bodyTarget.innerHTML = html;
            }
        } catch (error) {
            if (abort.signal.aborted) {
                return;
            }
            this.titleTarget.textContent = 'Erreur';
            this.bodyTarget.textContent = 'Erreur de connexion au serveur. Veuillez ré-essayer.';
        }
    }
}
