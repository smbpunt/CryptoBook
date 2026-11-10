import { Controller } from '@hotwired/stimulus';
import { visit } from '@hotwired/turbo';

/* stimulusFetch: 'lazy' */
/* Buttons of the "Générer mes positions" and "DCA Auto" dialogs (opening is handled by the dialog controller). */
export default class extends Controller {
    static targets = ['amount', 'autoForm'];

    static values = {
        generateUrl: String,
        autoUrl: String,
    };

    generate(event) {
        event.preventDefault();
        visit(`${this.generateUrlValue}/${this.amountTarget.value || 0}`);
    }

    auto(event) {
        event.preventDefault();
        if (!this.autoFormTarget.reportValidity()) {
            return;
        }
        const form = new FormData(this.autoFormTarget);
        const params = new URLSearchParams({
            crypto: form.get('dca_auto[coin]'),
            t: form.get('dca_auto[type_recurr]'),
            nb: form.get('dca_auto[nb_recurr]'),
            total: form.get('dca_auto[value]'),
            d: form.get('dca_auto[date_first]'),
        });
        visit(`${this.autoUrlValue}?${params}`);
    }
}
