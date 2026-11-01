import { Controller } from '@hotwired/stimulus';

// Démonstrateur UI : pas d'appel réseau. Le serveur Symfony prendra ensuite en charge POST/PATCH.
export default class extends Controller {
  static targets = ['blocks', 'errors', 'status'];
  connect() {
    this.counter = 0;
    this.blocksTarget.addEventListener('click', e => this.handleClick(e));
    this.blocksTarget.addEventListener('change', e => {
      if (e.target.matches('[data-field="separator"]')) e.target.closest('[data-block]').dataset.separator = String(e.target.checked);
    });
    if (!this.blocksTarget.children.length) {
      ['Échauffement', 'Corps de séance', 'Retour au calme'].forEach((name, i) => this.appendBlock(name, i === 1));
    }
  }
  template(id) { return document.getElementById(id).content.cloneNode(true); }
  addBlock() { this.appendBlock('Nouvelle partie', true); }
  appendBlock(name, withActivity = true) {
    const fragment = this.template('tf-block-template');
    const block = fragment.querySelector('[data-block]');
    block.dataset.uid = `b${++this.counter}`;
    block.querySelector('[data-field="name"]').value = name;
    this.blocksTarget.append(fragment);
    if (withActivity) this.appendActivity(block);
  }
  appendActivity(block) {
    const tbody = block.querySelector('[data-role="activity-list"]');
    const fragment = this.template('tf-activity-template');
    const rows = Array.from(fragment.children);
    const id = `a${++this.counter}`;
    rows.forEach(row => row.dataset.uid = id);
    tbody.append(fragment);
  }
  handleClick(e) {
    const button = e.target.closest('button[data-role]');
    if (!button) return;
    const role = button.dataset.role;
    const block = button.closest('[data-block]');
    const row = button.closest('[data-activity]');
    if (role === 'fold') {
      const content = block.querySelector('[data-role="block-content"]');
      content.hidden = !content.hidden;
      button.setAttribute('aria-expanded', String(!content.hidden));
      button.textContent = content.hidden ? '▸ Déplier' : '▾ Replier';
    } else if (role === 'add-activity') this.appendActivity(block);
    else if (role === 'remove-block') block.remove();
    else if (role === 'remove-activity') {
      const next = row.nextElementSibling;
      if (next?.matches('[data-hydration]') && next.dataset.uid === row.dataset.uid) next.remove();
      row.remove();
    } else if (role === 'remove-consigne') {
      const cell = row.querySelector('[data-role="consigne-cell"]');
      cell.classList.add('tf-merged');
      cell.querySelector('textarea').value = '';
      cell.querySelector('textarea').disabled = true;
      cell.querySelector('button').remove();
      // La cellule objectif gagne visuellement de la place via colspan.
      cell.hidden = true;
      row.querySelector('[data-role="objective-cell"]').colSpan = 2;
    } else if (role === 'remove-exercise') {
      const cell = button.closest('[data-role="exercise-cell"]');
      const other = [...row.querySelectorAll('[data-role="exercise-cell"]')].filter(c => c !== cell && !c.hidden)[0];
      cell.querySelector('textarea').value = '';
      cell.querySelector('textarea').disabled = true;
      cell.hidden = true;
      if (other) other.colSpan = Number(other.colSpan || 1) + 1;
      // Les deux tireurs peuvent être retirés; le serveur vérifiera la contrainte métier.
    }
  }
  save() {
    const errors = [];
    const theme = this.element.querySelector('[data-field="theme"]');
    this.element.querySelectorAll('.tf-invalid').forEach(el => el.classList.remove('tf-invalid'));
    if (!theme.value.trim()) errors.push({field:theme, message:'Le thème est obligatoire.'});
    const blocks = [...this.blocksTarget.querySelectorAll('[data-block]')].map((block, bi) => {
      const name = block.querySelector('[data-field="name"]');
      if (!name.value.trim()) errors.push({field:name, message:`Partie ${bi + 1} : nom obligatoire.`});
      const activities = [...block.querySelectorAll('[data-activity]')].map((row, ai) => {
        const read = field => row.querySelector(`[data-field="${field}"]`)?.value ?? '';
        const duration = row.querySelector('[data-field="duration"]');
        if (!duration.value.trim()) errors.push({field:duration, message:`Partie ${bi+1}, ligne ${ai+1} : durée obligatoire.`});
        const activeExercises = [...row.querySelectorAll('[data-role="exercise-cell"]')].filter(c => !c.hidden);
        if (!activeExercises.length) errors.push({field:duration, message:`Partie ${bi+1}, ligne ${ai+1} : au moins un tireur/exercice est nécessaire.`});
        const hydration = row.nextElementSibling?.matches('[data-hydration]') ? Number(row.nextElementSibling.querySelector('input').value || 0) : 0;
        return { description:read('description'), tireurA:read('tireurA'), tireurB:read('tireurB'), instruction:read('instruction'), objective:read('objective'), duration:read('duration'), hydration };
      });
      return {name:name.value, separator:block.querySelector('[data-field="separator"]').checked, activities};
    });
    const payload = {theme:theme.value, blocks};
    if (errors.length) {
      this.errorsTarget.hidden = false;
      this.errorsTarget.textContent = `${errors.length} erreur(s) : ${errors.map(e => e.message).join(' ')}`;
      errors.forEach(({field}) => {
        field.classList.add('tf-invalid');
        field.setAttribute('aria-invalid','true');
        const content = field.closest('[data-role="block-content"]');
        if (content?.hidden) {content.hidden=false;const fold=content.closest('[data-block]').querySelector('[data-role="fold"]');fold.setAttribute('aria-expanded','true');fold.textContent='▾ Replier';}
      });
      errors[0].field.focus();
      return;
    }
    this.errorsTarget.hidden = true;
    this.statusTarget.textContent = 'Données validées côté interface ; aucun enregistrement serveur effectué.';
    this.element.dispatchEvent(new CustomEvent('trainfactory:session-ready', {detail:payload, bubbles:true}));
    console.info('TrainFactory - payload prêt pour API', payload);
  }
}
