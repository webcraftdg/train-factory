import { Component, computed, input, output, signal } from '@angular/core';

import { Exercise } from '../../features/sessions/models/session';

@Component({
  selector: 'app-exercise-selector',
  standalone: true,
  templateUrl: './exercise-selector.html'
})
export class ExerciseSelectorComponent {
  readonly exercises = input.required<Exercise[]>();

  readonly selected = output<Exercise>();
  readonly closed = output<void>();
  readonly createRequested = output<void>();

  readonly search = signal('');

  readonly filteredExercises = computed(() => {
    const query = this.search().trim().toLowerCase();

    if (!query) return this.exercises();

    return this.exercises().filter(exercise =>
      exercise.name.toLowerCase().includes(query) ||
      exercise.description.toLowerCase().includes(query)
    );
  });

  selectExercise(exercise: Exercise): void {
    this.selected.emit(exercise);
  }

  requestCreate(): void {
    this.createRequested.emit();
  }
}
