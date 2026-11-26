
import {
  FormArray,
  FormBuilder,
  FormGroup,
  Validators,
  AbstractControl,
  ValidationErrors
} from '@angular/forms';

import {
  Block,
  Activity,
  Exercise
} from '../models/session';

const durationPattern = /^(?:\d+|\[\d+\+\d+\]|\d+\[\d+\+\d+\])$/;

function atLeastOneActivity(
  control: AbstractControl
): ValidationErrors | null {
  return (control as FormArray).length > 0
    ? null
    : { required: true };
}

export function createExerciseForm(
  fb: FormBuilder,
  exercise?: Exercise
): FormGroup {
  return fb.group({
    id: fb.control<number | null>(exercise?.id ?? null),

    name: fb.nonNullable.control(exercise?.name ?? '', [
      Validators.required,
      Validators.pattern(/\S/)
    ]),

    description: fb.nonNullable.control(exercise?.description ?? '', [
      Validators.required,
      Validators.pattern(/\S/)
    ])
  });
}

export function createActivityForm(
  fb: FormBuilder,
  activity?: Activity
): FormGroup {
  return fb.group({
    id: fb.control<number | null>(activity?.id ?? null),

    name: fb.nonNullable.control(activity?.name ?? ''),

    description: fb.nonNullable.control(activity?.description ?? ''),

    objective: fb.nonNullable.control(activity?.objective ?? '', [
      Validators.required,
      Validators.pattern(/\S/)
    ]),

    instruction: fb.nonNullable.control(activity?.instruction ?? '', [
      Validators.required,
      Validators.pattern(/\S/)
    ]),

    duration: fb.nonNullable.control(activity?.duration ?? '', [
      Validators.required,
      Validators.pattern(durationPattern)
    ]),
    hydration: fb.nonNullable.control(activity?.hydration ?? false),
    separatorAfter: [activity?.separatorAfter ?? false],
    exercises: fb.array<FormGroup>(
      (activity?.exercises ?? []).map(exercise =>
        createExerciseForm(fb, exercise)
      )
    )
  });
}

export function createBlockForm(
  fb: FormBuilder,
  block?: Block
): FormGroup {
  return fb.group({
    id: fb.control<number | null>(block?.id ?? null),

    name: fb.nonNullable.control(block?.name ?? ''),

    description: fb.nonNullable.control(block?.description ?? '', [
      Validators.required,
      Validators.pattern(/\S/)
    ]),

    objective: fb.nonNullable.control(block?.objective ?? ''),

    instruction: fb.nonNullable.control(block?.instruction ?? ''),

    activities: fb.array<FormGroup>(
      (block?.activities ?? []).map(activity =>
        createActivityForm(fb, activity)
      ),
      [atLeastOneActivity]
    )
  });
}
