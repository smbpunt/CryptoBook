import './stimulus_bootstrap.js';

import '@fontsource-variable/inter/index.css';
import '@fontsource-variable/space-grotesk/index.css';
import 'choices.js/public/assets/styles/choices.min.css';
import './styles/app.css';

// Turbo caches the page as it is when leaving it: close what is open, so the back button shows it closed.
document.addEventListener('turbo:before-cache', () => {
    document.querySelectorAll('dialog[open]').forEach((dialog) => dialog.close());
    document
        .querySelectorAll('[data-popover-open-value="true"]')
        .forEach((popover) => popover.setAttribute('data-popover-open-value', 'false'));
});
