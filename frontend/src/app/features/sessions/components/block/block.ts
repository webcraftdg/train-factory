
import { Component, input, output, signal, inject} from '@angular/core';
import {
  FormArray,
  FormBuilder,
  FormGroup,
  ReactiveFormsModule
} from '@angular/forms';

import { ActivityComponent } from '../activity/activity';
import { createActivity } from '../../models/session';
import { ExerciseService } from '../../../../core/api/exercises';
import { ExerciseSelectorComponent } from '../../../../components/exercise-selector/exercise-selector';
import { ExerciseCreateComponent } from '../../../../components/exercise-create/exercise-create';

import {
  createActivityForm
} from '../../forms/session-form.factory';

const durationPattern = /^(?:\d+|\[\d+\+\d+\]|\d+\[\d+\+\d+\])$/;
@Component({
  selector: 'app-block',
  standalone: true,
  imports: [ReactiveFormsModule, ActivityComponent, ExerciseSelectorComponent, ExerciseCreateComponent],
  templateUrl: './block.html',
  styleUrl: './block.css'
})
export class BlockComponent {
  readonly form = input.required<FormGroup>();
  readonly index = input.required<number>();
  readonly remove = output<void>();

  private readonly exerciseService = inject(ExerciseService);
  readonly availableExercises = this.exerciseService.exercises;


  constructor(private readonly fb: FormBuilder) {}

  readonly collapsed = signal(false);

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

    this.activities.push(activityForm);
  }

  removeActivity(index: number): void {
    this.activities.removeAt(index);
  }
}
