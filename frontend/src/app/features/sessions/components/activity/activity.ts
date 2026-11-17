
import { Component, input, output, signal } from '@angular/core';
import {
  FormArray,
  FormControl,
  FormGroup,
  ReactiveFormsModule
} from '@angular/forms';

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
  readonly labelA = signal('Tireur A');
  readonly labelB = signal('Tireur B');
  readonly showInstruction = signal(true);

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
      new FormGroup({
        id: new FormControl<number | null>(null),
        name: new FormControl('Tireur B', { nonNullable: true }),
        description: new FormControl('', { nonNullable: true })
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
