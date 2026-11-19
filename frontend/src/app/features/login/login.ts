import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { Auth } from '../../core/auth/auth';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [ReactiveFormsModule],
  templateUrl: './login.html',
  styleUrl: './login.css'
})
export class Login {
    private readonly fb = inject(FormBuilder);
    private readonly auth = inject(Auth);
    private readonly router = inject(Router);

    readonly loading = signal(false);
    readonly errorMessage = signal<string | null>(null);

    readonly form = this.fb.nonNullable.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required]]
    });

    onSubmit(): void {
      if (this.form.invalid) {
        this.form.markAllAsTouched();
        return;
      }

      this.loading.set(true);
      this.errorMessage.set(null);

      const { email, password } = this.form.getRawValue();

      this.auth.login(email, password).subscribe({
        next: () => {
          this.router.navigateByUrl('/tableau-de-bord');
        },
        error: () => {
          this.errorMessage.set('Identifiants incorrects ou erreur de connexion.');
          this.loading.set(false);
        }
      });
    }

}
