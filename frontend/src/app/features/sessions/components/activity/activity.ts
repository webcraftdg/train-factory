
import { Component, input, output, signal } from '@angular/core';
import {
  FormArray,
  FormBuilder,
  FormControl,
  FormGroup,
  ReactiveFormsModule,
  Validators
} from '@angular/forms';

import { createExerciseForm } from '../../forms/session-form.factory';

type ExerciseForm = FormGroup<{
  id: FormControl<number | null>;
  name: FormControl<string>;
  description: FormControl<string>;
}>;

@Component({
  selector: 'tr[app-activity]',
  standalone: true,
  imports: [ReactiveFormsModule],
  templateUrl: './activity.html',
  styleUrl: './activity.css'
})
export class ActivityComponent {
  readonly form = input.required<FormGroup>();
  readonly remove = output<void>();
  readonly showInstruction = signal(true);

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
    if (this.exercises.length >= 2) return;

    this.exercises.push(
      createExerciseForm(this.fb, {
        id: null,
        name: 'Tireur',
        description: ''
      })
    );
  }


  removeExercise(index: number): void {
    // Une activité de travail doit conserver un exercice.
    if (this.exercises.length <= 1) return;

    this.exercises.removeAt(index);
  }

  removeInstruction(): void {
    this.form().get('instruction')?.setValue('');
    this.showInstruction.set(false);
  }

  restoreInstruction(): void {
    this.showInstruction.set(true);
  }
}
