
import { Component, input, output, signal} from '@angular/core';
import {
  FormArray,
  FormBuilder,
  FormGroup,
  ReactiveFormsModule
} from '@angular/forms';

import { ActivityComponent } from '../activity/activity';
import { createActivity } from '../../models/session';

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

  addActivity(hydration = false): void {
    const activity = createActivity();

    const activityForm = this.fb.group({
      id: this.fb.control<number | null>(null),
      name: this.fb.nonNullable.control(
        hydration ? 'Hydratation' : activity.name
      ),
      description: this.fb.nonNullable.control(activity.description),
      objective: this.fb.nonNullable.control(activity.objective),
      instruction: this.fb.nonNullable.control(activity.instruction),
      duration: this.fb.nonNullable.control(
        hydration ? '2 minutes' : activity.duration
      ),
      hydration: this.fb.nonNullable.control(hydration),
      exercises: this.fb.array<FormGroup>([])
    });

   if (!hydration) {
    for (let i = 0; i < 2; i++) {
      activityForm.controls.exercises.push(
        this.fb.group({
          id: this.fb.control<number | null>(null),
          name: this.fb.nonNullable.control(''),
          description: this.fb.nonNullable.control('')
        })
      );
    }
  }

    this.activities.push(activityForm);
  }

  removeActivity(index: number): void {
    this.activities.removeAt(index);
  }
}
