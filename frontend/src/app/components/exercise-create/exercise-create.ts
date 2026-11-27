import { Component, inject, output, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';
import { ExerciseService } from '../../core/api/exercises';
import { Exercise } from '../../features/sessions/models/session';
import { createExerciseForm } from '../../features/sessions/forms/session-form.factory';
import { HttpErrorResponse } from '@angular/common/http';

@Component({
  imports: [ReactiveFormsModule],
  selector: 'app-exercise-create',
  styleUrl: './exercise-create.css',
  templateUrl: './exercise-create.html',
})
export class ExerciseCreateComponent {
    private readonly fb = inject(FormBuilder);
    private readonly exerciseService = inject(ExerciseService);

    readonly form = createExerciseForm(this.fb);

    readonly created = output<Exercise>();
    readonly closed = output<void>();

    readonly saving = signal(false);
    readonly error = signal<string | null>(null);

    save(): void {
      if (this.saving()) return;
      if (this.form.invalid) {
        this.form.markAllAsTouched();
        requestAnimationFrame(() => {
          const firstInvalid = document.querySelector<HTMLElement>(
            'input.ng-invalid, textarea.ng-invalid, select.ng-invalid'
          );

          firstInvalid?.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
          });

          firstInvalid?.focus({ preventScroll: true });
        });
        return;
      }

      const exercise = this.form.getRawValue() as Exercise;
      this.saving.set(true);
      this.error.set(null);
      const request = this.exerciseService.create(exercise);

      request.subscribe({
        next: (response) => {
          this.saving.set(false);
          this.created.emit(response);
          console.log('Séance enregistrée :', response);
        },

        error: (error: HttpErrorResponse) => {
          this.saving.set(false);
          console.error('Erreur serveur :', error);
          this.error.set(
            error.error?.message ?? "Impossible d'enregistrer l'exercice."
          );
        }
      });
      console.log('Exercice JSON', exercise);
    }

}
