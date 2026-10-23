
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['select', 'picker', 'tags'];
    initialSelection = [];
    connect() {
         Array.from(this.selectTarget.selectedOptions).forEach((option) => {
            this.initialSelection.push(option.value);
         });
         console.log(this.initialSelection);
        this.render();
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
        }

        this.render();
    }
    clear() {
        Array.from(this.selectTarget.options).forEach((option) => {
            option.selected = false;
        });
        this.render();
    }

    reset() {
        Array.from(this.selectTarget.options).forEach((option) => {
            option.selected = this.initialSelection.includes(option.value);
        });
        this.render();
    }

    render() {
        this.renderTags();
        this.renderPicker();
    }

    renderTags() {
        this.tagsTarget.replaceChildren();

        const selected = Array.from(this.selectTarget.selectedOptions);

        if (selected.length === 0) {
            const empty = document.createElement('span');

            empty.className = 'text-sm text-slate-400';
            empty.textContent = 'Aucun exercice sélectionné.';

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

            const removeButton = document.createElement('button');

            removeButton.type = 'button';
            removeButton.dataset.value = option.value;
            removeButton.dataset.action = 'exercise-selector#remove';

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

    renderPicker() {
        this.pickerTarget.replaceChildren();

        const placeholder = document.createElement('option');

        placeholder.value = '';
        placeholder.textContent = 'Sélectionner un exercice...';

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
