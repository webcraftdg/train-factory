
import { Component, input, output, signal } from '@angular/core';
import {
  FormArray,
  FormBuilder,
  FormControl,
  FormGroup,
  ReactiveFormsModule
} from '@angular/forms';
import type { Exercise } from '../../models/session';
import { createExerciseForm } from '../../forms/session-form.factory';

type ExerciseForm = FormGroup<{
  id: FormControl<number | null>;
  name: FormControl<string>;
  description: FormControl<string>;
}>;

@Component({
  selector: 'tr[app-activity]',
  exportAs: 'appActivity',
  standalone: true,
  imports: [ReactiveFormsModule],
  templateUrl: './activity.html',
  styleUrl: './activity.css'
})
export class ActivityComponent {
  readonly form = input.required<FormGroup>();
  readonly remove = output<void>();
  readonly showInstruction = signal(true);
  readonly exerciseCount = signal(0);
  readonly exerciseSelectorOpen = signal(false);

  constructor(private readonly fb: FormBuilder) {}

  get exercises(): FormArray<ExerciseForm> {
    return this.form().get('exercises') as FormArray<ExerciseForm>;
  }

  get hydration(): boolean {
    return this.form().get('hydration')?.value === true;
  }

  toggleHydration(checked: boolean): void {
    this.form().get('hydration')?.setValue(checked);
  }

  addExercise(): void {
    const length = this.exercises.length;
    if (length >= 2) return;

    const name = length === 0 ? 'Tireur A' : 'Tireur B';

    this.exercises.push(
      createExerciseForm(this.fb, {
        id: null,
        name,
        description: ''
      })
    );
    this.exerciseCount.set(this.exercises.length);
  }

  selectExistingExercise(exercise: Exercise): void {
    if (this.exercises.length >= 2) return;

    this.exercises.push(
      createExerciseForm(this.fb, {
        id: exercise.id,
        name: exercise.name,
        description: exercise.description
      })
    );

    this.exerciseCount.set(this.exercises.length);
    this.exerciseSelectorOpen.set(false);
  }


  openExerciseSelector(): void {
    if (this.exercises.length >= 2) return;

    this.exerciseSelectorOpen.set(true);
  }


  removeExercise(index: number): void {
    // Une activité de travail doit conserver un exercice.
    if (this.exercises.length <= 1) return;

    this.exercises.removeAt(index);
    this.exerciseCount.set(this.exercises.length);
  }

  removeInstruction(): void {
    this.form().get('instruction')?.setValue('');
    this.showInstruction.set(false);
  }

  restoreInstruction(): void {
    this.showInstruction.set(true);
  }
}
