import { Injectable, inject, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { tap } from 'rxjs';
import { Api } from '../api/api';

interface LoginResponse {
  token: string;
}

@Injectable({
  providedIn: 'root'
})
export class Auth {
    private readonly http = inject(HttpClient);
    readonly authenticated = signal(this.getToken() !== null);
    readonly roles = signal<string[]>(
        this.extractRoles(this.getToken())
    );

    login(email: string, password: string) {
      return this.http.post<LoginResponse>(
        Api.AUTHENTICATION,
        { email, password }
      ).pipe(
        tap(response => {
            sessionStorage.setItem('access_token', response.token);
            this.roles.set(this.extractRoles(response.token));
            this.authenticated.set(true);
        })
      );
    }

    getToken(): string | null {
      return sessionStorage.getItem('access_token');
    }
    refreshToken() {
      return this.http.post<LoginResponse>(
        Api.REFRESH_TOKEN,
        {},
        { withCredentials: true }
      ).pipe(
        tap(response => {
            sessionStorage.setItem('access_token', response.token);
            this.roles.set(this.extractRoles(response.token));
            this.authenticated.set(true);
        })
      );
    }

    isAuthenticated(): boolean {
      return this.getToken() !== null;
    }

    logout(): void {
      sessionStorage.removeItem('access_token');
      this.roles.set([]);
      this.authenticated.set(false);
    }

    private extractRoles(token: string | null): string[] {
        if (!token) {
            return [];
        }

        try {
            const payload = token.split('.')[1]
                .replace(/-/g, '+')
                .replace(/_/g, '/');

            const decoded = JSON.parse(atob(payload));

            return Array.isArray(decoded.roles)
                ? decoded.roles.filter(
                    (role: unknown) => typeof role === 'string'
                )
                : [];
        } catch {
            return [];
        }
    }
}
