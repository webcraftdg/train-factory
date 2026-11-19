
import { Component, input, output, signal} from '@angular/core';
import {
  FormArray,
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators
} from '@angular/forms';

import { ActivityComponent } from '../activity/activity';
import { createActivity } from '../../models/session';

import {
  createActivityForm,
  createExerciseForm
} from '../../forms/session-form.factory';

const durationPattern = /^(?:\d+|\[\d+\+\d+\]|\d+\[\d+\+\d+\])$/;
@Component({
  selector: 'app-block',
  standalone: true,
  imports: [ReactiveFormsModule, ActivityComponent],
  templateUrl: './block.html',
  styleUrl: './block.css'
})
export class BlockComponent {
  readonly form = input.required<FormGroup>();
  readonly index = input.required<number>();
  readonly remove = output<void>();



  constructor(private readonly fb: FormBuilder) {}

  readonly collapsed = signal(false);
  readonly separator = signal(false);

  toggleSeparator(): void {
    this.separator.update(value => !value);
  }


  get activities(): FormArray<FormGroup> {
    return this.form().get('activities') as FormArray<FormGroup>;
  }

  addActivity(): void {
    const activity = createActivity();

    const blockName = this.form().get('name')?.value || 'Bloc';
    const activityNumber = this.activities.length + 1;

    activity.name = `${blockName} - Activité ${activityNumber}`;

    // Création de l'activité avec ses validateurs
    const activityForm = createActivityForm(this.fb, activity);

    // Ajout des deux exercices par défaut
    const exercises = activityForm.get('exercises') as FormArray<FormGroup>;

    exercises.push(
      createExerciseForm(this.fb, {
        id: null,
        name: 'Tireur A',
        description: ''
      })
    );

    exercises.push(
      createExerciseForm(this.fb, {
        id: null,
        name: 'Tireur B',
        description: ''
      })
    );

    this.activities.push(activityForm);
  }

  removeActivity(index: number): void {
    this.activities.removeAt(index);
  }
}
