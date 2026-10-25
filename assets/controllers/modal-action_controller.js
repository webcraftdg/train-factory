import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
        static targets = [
            'modal', 'dialog', 'buttonClose', 'content'
        ];
        static values = {
            url:String,
            eventName: { type: String, default: 'items-selector_update'},
        };

    connect() {
        this.close();
    }

    async prepareHtml() {
        try {
            if (this.urlValue) {
                const response = await fetch(this.urlValue, {method:'GET'});
                if (!response.ok) {
                    throw new Error(`Erreur HTTP ${response.status}`);
                }
                const html  = await response.text();
                if(this.dialogTarget && this.contentTarget) {
                    this.contentTarget.innerHTML = html;
                    this.dialogTarget.showModal();
                    const form = this.contentTarget.querySelector('form');
                    if (form) {
                        this.subscribeForm(form);
                    }
                }
            }
        } catch(error) {
            console.log(error);
        }
    }

    async open(event) {
        this.urlValue = event.detail?.url ?? this.urlValue;
        return this.prepareHtml();
    }

    async submit(event) {
        event.preventDefault();

        const form = event.currentTarget;
        const formData = new FormData(form);

        const response = await fetch(form.action, {
            method: form.method,
            body: formData
        });

        if (response.status === 422) {
            this.contentTarget.innerHTML = await response.text();
            // Il faut réinstaller son écouteur submit.
            const form = this.contentTarget.querySelector('form');
            this.subscribeForm(form);
            // name="assignment[user]
            return;
        }

        if (response.status === 201) {
            const result = await response.json();
            if (result) {
                this.dispatch(this.eventNameValue, {
                    detail: result
                });
            }

            this.close();
        }
    }

    close() {
        this.dialogTarget.close();
        this.contentTarget.innerHTML = '';
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

    subscribeForm(form) {
        form?.addEventListener('submit', this.submit.bind(this));
    }
}
