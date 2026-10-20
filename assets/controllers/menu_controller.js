import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
       static targets = ['menu', 'button'];

    connect() {
        this.close();
    }

    toggle() {
        const isOpen = this.buttonTarget.getAttribute('aria-expanded') === 'true';

        if (isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        this.menuTarget.classList.remove('hidden');
        this.buttonTarget.setAttribute('aria-expanded', 'true');
        this.buttonTarget.setAttribute('aria-label', 'Fermer le menu');
    }

    close() {
        this.menuTarget.classList.add('hidden');
        this.buttonTarget.setAttribute('aria-expanded', 'false');
        this.buttonTarget.setAttribute('aria-label', 'Ouvrir le menu');
    }

    closeOnEscape(event) {
        if (event.key === 'Escape') {
            this.close();
        }
    }

    closeOnOutsideClick(event) {
        if (!this.element.contains(event.target)) {
            this.close();
        }
    }
}
