import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        name: String,
        width: { type: Number, default: 24 },
        height: { type: Number, default: 24 },
    }

    async connect() {
        const response = await fetch(`/icons/${this.nameValue}.svg`);

        if (!response.ok) {
            console.error(`Icône "${this.nameValue}" introuvable.`);
            return;
        }

        let svg = await response.text();

        const template = document.createElement('template');
        template.innerHTML = svg.trim();
        const finalSvg = template.content.querySelector('svg');
        finalSvg.setAttribute('width', this.widthValue);
        finalSvg.setAttribute('height', this.heightValue);

        this.element.innerHTML = finalSvg.outerHTML;
    }
}
