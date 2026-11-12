import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
/* Shows or hides the detail row named by the button's `aria-controls`. */
export default class extends Controller {
    toggle(event) {
        const button = event.currentTarget;
        const detail = document.getElementById(button.getAttribute('aria-controls'));
        const expanded = detail.classList.toggle('hidden') === false;
        button.setAttribute('aria-expanded', String(expanded));
    }
}
