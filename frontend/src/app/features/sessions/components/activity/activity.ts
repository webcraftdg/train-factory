
import { Component, input, output, signal, OnInit } from '@angular/core';
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
  host: { '[class.mobile-collapsed]': 'mobileCollapsed() && !(form().invalid && form().touched)' },
  standalone: true,
  imports: [ReactiveFormsModule],
  templateUrl: './activity.html',
  styleUrl: './activity.css'
})
export class ActivityComponent  implements OnInit {
  readonly form = input.required<FormGroup>();
  readonly remove = output<void>();
  readonly mobileIndex = input(0);
  readonly mobileCollapsed = signal(false);
  readonly showInstruction = signal(true);
  readonly exerciseCount = signal(0);
  readonly exerciseSelectorOpen = signal(false);
  readonly exerciseCreateOpen = signal(false);
  constructor(private readonly fb: FormBuilder) {}

  get exercises(): FormArray<ExerciseForm> {
    return this.form().get('exercises') as FormArray<ExerciseForm>;
  }

  get hydration(): boolean {
    return this.form().get('hydration')?.value === true;
  }

  ngOnInit(): void {
    this.exerciseCount.set(this.exercises.length);
  }

  toggleHydration(checked: boolean): void {
    this.form().get('hydration')?.setValue(checked);
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

  openExerciseCreate(): void {
      if (this.exercises.length >= 2) return;

      this.exerciseSelectorOpen.set(false);
      this.exerciseCreateOpen.set(true);
  }

  cancelExerciseCreate(): void {
      this.exerciseCreateOpen.set(false);
      this.openExerciseSelector();
  }

  onExerciseCreated(exercise: Exercise): void {
    this.exerciseCreateOpen.set(false);
    this.selectExistingExercise(exercise);
  }


  openExerciseSelector(): void {
    if (this.exercises.length >= 2) return;

    this.exerciseSelectorOpen.set(true);
  }


  removeExercise(index: number): void {
    // Une activité de travail doit conserver un exercice.
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
