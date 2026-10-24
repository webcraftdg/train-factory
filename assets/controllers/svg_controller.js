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

        svg = svg.replace('<svg', `<svg width="${this.widthValue}" height="${this.heightValue}"`);

        this.element.innerHTML = svg;
    }
}
