import { Component, inject, signal } from '@angular/core';
import { Router, RouterLink, RouterLinkActive} from '@angular/router';
import { Auth } from '../../../core/auth/auth';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [RouterLink, RouterLinkActive],
  templateUrl: './header.html',
  styleUrl: './header.css'
})
export class Header {
  readonly auth = inject(Auth);
  private readonly router = inject(Router);

  readonly mobileMenuOpen = signal(false);

  logout(): void {
    this.mobileMenuOpen.set(false);
    this.auth.logout();
    this.router.navigateByUrl('/login');
  }
}
