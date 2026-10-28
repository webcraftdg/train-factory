
import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

export default class extends Controller {
    static targets = ['select', 'picker', 'tags', 'cards', 'order'];

    static values = {
        mode: { type: String, default: 'tags' },
        draggable: {type:Boolean, default: false},
        url: String
    };
    initialSelection = [];
    sortingSelectionIds = [];
    sortable = null;
    containerTarget = null;
    connect() {

        if (this.orderTarget.value) {
            this.sortingSelectionIds = JSON.parse(this.orderTarget.value);
        }
        let initialOrderIds = [];
        Array.from(this.selectTarget.selectedOptions).forEach((option) => {
            this.initialSelection.push(option.value);
            initialOrderIds.push(option.value);
        });
        if (this.sortingSelectionIds.length <= 0) {
            this.sortingSelectionIds = initialOrderIds;
        }
        console.log(this.initialSelection);
        if (this.modeValue === 'tags') {
            this.cardsTarget.classList.add('hidden');;
            this.tagsTarget.classList.remove('hidden');;
        } else {
            this.tagsTarget.classList.add('hidden');;
            this.cardsTarget.classList.remove('hidden');;
        }
        if (this.draggableValue === true) {
            this.containerTarget = this.cardsTarget;
            if (this.modeValue === 'tags') {
                this.containerTarget = this.tagsTarget;
            }
            this.sortable = Sortable.create(this.containerTarget, {
                animation: 150,
                onEnd: () => this.updateOrder()
            });
        }

        this.render();
    }

    updateOrder() {
        this.sortingSelectionIds = Array.from(
            this.containerTarget.querySelectorAll('[data-item-id]')
        ).map(element => Number(element.dataset.itemId));

        this.orderTarget.value = JSON.stringify(this.sortingSelectionIds);
    }

    disconnect() {
        this.sortable?.destroy();
    }

    add() {
        const value = this.pickerTarget.value;

        if (!value) {
            return;
        }

        const option = Array.from(this.selectTarget.options)
            .find(option => option.value === value);

        if (option) {
            option.selected = true;
            this.sortingSelectionIds.push(option.value);
        }

        this.pickerTarget.value = '';

        this.render();
    }

    remove(event) {
        const value = event.currentTarget.dataset.value;

        const option = Array.from(this.selectTarget.options)
            .find(option => option.value === value);

        if (option) {
            option.selected = false;
            const index = this.sortingSelectionIds.indexOf(option.value);
            this.sortingSelectionIds.splice(index, 1);
        }

        this.render();
    }
    clear() {
        Array.from(this.selectTarget.options).forEach((option) => {
            option.selected = false;
        });
        this.sortingSelectionIds.splice(0, this.sortingSelectionIds.length);
        this.render();
    }

    reset() {
        Array.from(this.selectTarget.options).forEach((option) => {
            option.selected = this.initialSelection.includes(option.value);
        });
        this.render();
    }

    updateSelect(event) {
        if (event.detail?.id && event.detail?.name) {
            const option = document.createElement("option");
            option.value = event.detail.id;
            option.text = event.detail.name;
            option.selected = true;
            this.selectTarget.add(option, null);
            this.render();
        }
    }

    render() {
        if (this.modeValue === 'tags') {
            this.renderTags();
        } else {
            this.renderCards();
        }
        this.renderPicker();
    }

    sortOptionsSelected() {
        const order = this.sortingSelectionIds || [];
        //create position
        const positions = new Map(
            order.map((id, index) => [Number(id), index])
        );
        //Get options selected
        const selectedOptions = Array.from(this.selectTarget.selectedOptions);

        //Sort option with positions
        selectedOptions.sort((a, b) => {
            const positionA = positions.get(Number(a.value)) ?? Infinity;
            const positionB = positions.get(Number(b.value)) ?? Infinity;

            return positionA - positionB;
        });

        return selectedOptions;
    }

    renderTags() {
        this.tagsTarget.replaceChildren();

        const selected = this.sortOptionsSelected();

        if (selected.length === 0) {
            const empty = document.createElement('span');

            empty.className = 'text-sm text-slate-400';
            empty.textContent = 'Aucune sélection.';

            this.tagsTarget.appendChild(empty);
            return;
        }

        selected.forEach(option => {
            const tag = document.createElement('span');

            tag.className =
                'inline-flex max-w-full items-center gap-2 rounded-lg ' +
                'bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700';

            const label = document.createElement('span');

            label.className = 'min-w-0 break-words';
            label.textContent = option.textContent.trim();
            if (Boolean(this.draggableValue) === true) {
                tag.setAttribute('draggable', 'true');
            }
            tag.dataset.itemId = option.value;

            const removeButton = document.createElement('button');

            removeButton.type = 'button';
            removeButton.dataset.value = option.value;
            removeButton.dataset.action = 'items-selector#remove';

            removeButton.className =
                'shrink-0 rounded p-0.5 hover:bg-blue-200 ' +
                'focus:outline-none focus:ring-2 focus:ring-blue-500';

            removeButton.setAttribute(
                'aria-label',
                `Retirer ${option.textContent.trim()}`
            );

            removeButton.textContent = '×';

            tag.append(label, removeButton);
            this.tagsTarget.appendChild(tag);
        });
    }

    renderCards() {
        this.cardsTarget.replaceChildren();

        const selected = this.sortOptionsSelected();

        if (selected.length === 0) {
            const empty = document.createElement('span');

            empty.className = 'text-sm text-slate-400';
            empty.textContent = 'Aucune sélection.';

            this.cardsTarget.appendChild(empty);
            return;
        }

        selected.forEach(option => {
            const id = option.value;
            const finalUrl = decodeURI(this.urlValue).replace('{id}', id);
            fetch(finalUrl, {
                method: 'GET'
            }).then((response) => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP ${response.status}`);
                }
                return response.text();
            }).then((html) => {
                const container = document.createElement('div');
                container.classList.add('w-full');
                container.classList.add('relative');
                container.dataset.itemId = option.value;

                container.innerHTML = html;
                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.dataset.value = option.value;
                removeButton.dataset.action = 'items-selector#remove';

                removeButton.className = [
                    'absolute -top-2.5 -right-2.5 z-10',
                    'flex size-7 items-center justify-center',
                    'rounded-full bg-red-500 text-white',
                    'border-2 border-white shadow-md',
                    'text-lg font-bold leading-none',
                    'transition-colors hover:bg-red-600',
                    'focus:outline-none focus:ring-2 focus:ring-red-400'
                ].join(' ');

                removeButton.setAttribute(
                    'aria-label',
                    `Retirer ${option.textContent.trim()}`
                );

                removeButton.textContent = '×';

                container.append(removeButton);
                this.cardsTarget.appendChild(container);
            }).catch((error) => {
                console.log(error);
            });
        });
    }

    renderPicker() {
        this.pickerTarget.replaceChildren();

        const placeholder = document.createElement('option');

        placeholder.value = '';
        placeholder.textContent = 'Sélectionner un élément...';

        this.pickerTarget.appendChild(placeholder);

        Array.from(this.selectTarget.options)
            .filter(option => option.value && !option.selected)
            .forEach(option => {
                const item = document.createElement('option');

                item.value = option.value;
                item.textContent = option.textContent;

                this.pickerTarget.appendChild(item);
            });
    }

}
