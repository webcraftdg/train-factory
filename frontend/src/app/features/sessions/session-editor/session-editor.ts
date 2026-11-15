
import { Component, inject, signal } from '@angular/core';
import {
  FormArray,
  FormBuilder,
  FormGroup,
  ReactiveFormsModule
} from '@angular/forms';

import {
  createBlock,
  Session
} from '../models/session';

import { BlockComponent } from '../components/block/block';

@Component({
  selector: 'app-session-editor',
  standalone: true,
  imports: [ReactiveFormsModule, BlockComponent],
  templateUrl: './session-editor.html',
  styleUrl: './session-editor.css'
})
export class SessionEditor {
  private readonly fb = inject(FormBuilder);

  readonly saving = signal(false);
  readonly error = signal<string | null>(null);

  readonly form = this.fb.group({
    id: this.fb.control<number | null>(null),
    name: this.fb.nonNullable.control(''),
    description: this.fb.nonNullable.control(''),
    target: this.fb.nonNullable.control(''),
    blocks: this.fb.array<FormGroup>([])
  });

  get blocks(): FormArray<FormGroup> {
    return this.form.controls.blocks;
  }

  addBlock(): void {
    const block = createBlock();

    const blockForm = this.fb.group({
      id: this.fb.control<number | null>(block.id),
      name: this.fb.nonNullable.control(block.name),
      description: this.fb.nonNullable.control(block.description),
      objective: this.fb.nonNullable.control(block.objective),
      instruction: this.fb.nonNullable.control(block.instruction),
      activities: this.fb.array<FormGroup>([])
    });

    this.blocks.push(blockForm);
  }

  removeBlock(index: number): void {
    this.blocks.removeAt(index);
  }

  onSubmit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    const session = this.form.getRawValue() as Session;

    console.log('Session JSON', session);
  }
}
