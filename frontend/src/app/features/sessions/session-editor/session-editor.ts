
import { Component, inject, signal} from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import {
  FormArray,
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators,
  AbstractControl,
  ValidationErrors
} from '@angular/forms';

import {
  createBlock,
  Session
} from '../models/session';

import { BlockComponent } from '../components/block/block';
import { SessionsService } from '../../../core/api/sessions';
import { HttpErrorResponse } from '@angular/common/http';
import { createBlockForm } from '../forms/session-form.factory';

function atLeastOneActivity(control: AbstractControl): ValidationErrors | null
{
  return control.value.length > 0
    ? null
    : { required: true };
}

@Component({
  selector: 'app-session-editor',
  standalone: true,
  imports: [ReactiveFormsModule, BlockComponent],
  templateUrl: './session-editor.html',
  styleUrl: './session-editor.css'
})
export class SessionEditor {
  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);

  readonly saving = signal(false);
  readonly error = signal<string | null>(null);
  private readonly route = inject(ActivatedRoute);
  private readonly sessionService = inject(SessionsService);

  constructor() {
    const sessionId = this.route.snapshot.paramMap.get('id');

    if (sessionId === null) {
      this.initializeDefaultBlocks();
      return;
    }

    this.sessionService.getOne(Number(sessionId)).subscribe({
      next: (session) => {
        this.loadSession(session);
        console.log('Séance reçue depuis Symfony :', session);
      },
      error: (error: HttpErrorResponse) => {
        console.error('Erreur GET :', error);
        this.error.set(
          error.error?.message ?? 'Impossible de charger la séance.'
        );
      }
    });
  }

  readonly form = this.fb.group({
    id: this.fb.control<number | null>(null),
    name: this.fb.nonNullable.control('', [
      Validators.required,
      Validators.pattern(/\S/)
    ]),
    description: this.fb.nonNullable.control('', [
      Validators.required,
      Validators.pattern(/\S/)
    ]),
    target: this.fb.nonNullable.control('', [
      Validators.required,
      Validators.pattern(/\S/)
    ]),
    blocks: this.fb.array<FormGroup>([])
  });

  get blocks(): FormArray<FormGroup> {
    return this.form.controls.blocks;
  }

  addBlock(name = ''): FormGroup {
    const block = createBlock();

    const blockForm = this.fb.group({
      id: this.fb.control<number | null>(null),
      name: this.fb.nonNullable.control(name || block.name),
      description: this.fb.nonNullable.control('', [
        Validators.required,
        Validators.pattern(/\S/)
      ]),
      objective: this.fb.nonNullable.control(block.objective),
      instruction: this.fb.nonNullable.control(block.instruction),
      activities: this.fb.array<FormGroup>(
      [],
      [atLeastOneActivity]
    )
    });

    this.blocks.push(blockForm);
    return blockForm;
  }

  private loadSession(session: Session): void {
    // Informations générales de la séance
    this.form.patchValue({
      id: session.id,
      name: session.name,
      description: session.description,
      target: session.target
    });

    // Suppression des blocs éventuellement présents
    this.blocks.clear();

    // Reconstruction complète de la séance
    for (const block of session.blocks) {
      this.blocks.push(
        createBlockForm(this.fb, block)
      );
    }
  }

  removeBlock(index: number): void {
    this.blocks.removeAt(index);
  }

  onSubmit(): void {
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

    const session = this.form.getRawValue() as Session;
    this.saving.set(true);
    this.error.set(null);
    const request = session.id === null ? this.sessionService.create(session) : this.sessionService.update(session.id, session);

    request.subscribe({
      next: (response) => {
        this.saving.set(false);
        console.log('Séance enregistrée :', response);
        this.router.navigate(['/seances']);
      },

      error: (error: HttpErrorResponse) => {
        this.saving.set(false);
        console.error('Erreur serveur :', error);
        this.error.set(
          error.error?.message ?? "Impossible d'enregistrer la séance."
        );
      }
    });
    console.log('Session JSON', session);
  }

  private initializeDefaultBlocks(): void {
      const defaultBlocks = [
        'Échauffement',
        'Corps de séance',
        'Retour au calme'
      ];

      for (const name of defaultBlocks) {
        this.addBlock(name);
      }
  }

}
