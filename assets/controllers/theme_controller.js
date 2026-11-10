import { Controller } from '@hotwired/stimulus';

/*
 * Light / dark switch. The inline script in base.html.twig sets the initial `.dark` class before the CSS loads;
 * this controller toggles it, stores the choice in localStorage and emits `theme:change` on window.
 * Without a stored choice, the theme follows the system setting.
 */
export default class extends Controller {
    static values = {
        lightLabel: String,
        darkLabel: String,
    };

    #media = window.matchMedia('(prefers-color-scheme: dark)');
    #onSystemChange = (event) => {
        if (this.#storedTheme() === null) {
            this.#apply(event.matches);
        }
    };

    connect() {
        this.#media.addEventListener('change', this.#onSystemChange);
        this.#updateLabel();
    }

    disconnect() {
        this.#media.removeEventListener('change', this.#onSystemChange);
    }

    toggle() {
        const dark = !document.documentElement.classList.contains('dark');
        try {
            localStorage.setItem('theme', dark ? 'dark' : 'light');
        } catch {
            // Storage blocked: the choice lasts until the next page load.
        }
        this.#apply(dark);
    }

    #apply(dark) {
        document.documentElement.classList.toggle('dark', dark);
        this.#updateLabel();
        window.dispatchEvent(new CustomEvent('theme:change', { detail: { theme: dark ? 'dark' : 'light' } }));
    }

    #updateLabel() {
        const dark = document.documentElement.classList.contains('dark');
        this.element.setAttribute('aria-label', dark ? this.lightLabelValue : this.darkLabelValue);
    }

    #storedTheme() {
        try {
            return localStorage.getItem('theme');
        } catch {
            return null;
        }
    }
}
