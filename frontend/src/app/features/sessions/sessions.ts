
import { Component, inject, OnInit, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import {
  SessionsService
} from '../../core/api/sessions';

import {Session} from '../sessions/models/session'

@Component({
  selector: 'app-sessions',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './sessions.html',
  styleUrl: './sessions.css'
})
export class Sessions implements OnInit {
  private readonly sessionsService = inject(SessionsService);

  readonly sessions = signal<Session[]>([]);
  readonly loading = signal(true);
  readonly deletingId = signal<number | null>(null);
  readonly downloadingId = signal<number | null>(null);
  readonly duplicatingId = signal<number | null>(null);
  readonly error = signal<string | null>(null);
  ngOnInit(): void {
    this.loadSessions();
  }

  loadSessions(): void {
    this.loading.set(true);
    this.error.set(null);

    this.sessionsService.getAll().subscribe({
      next: data => {
        this.sessions.set(data);
        this.loading.set(false);
      },
      error: () => {
        this.error.set('Impossible de charger les séances.');
        this.loading.set(false);
      }
    });
  }

  deleteSession(session: Session): void {
    if (!confirm(`Supprimer la séance « ${session.name} » ?`)) {
      return;
    }
    if (session.id) {
      this.deletingId.set(session.id);
      this.error.set(null);

      this.sessionsService.delete(session.id).subscribe({
        next: () => {
          this.sessions.update(items =>
            items.filter(item => item.id !== session.id)
          );
          this.deletingId.set(null);
        },
        error: () => {
          this.error.set('Impossible de supprimer cette séance.');
          this.deletingId.set(null);
        }
      });
    }
  }

  downloadSession(session: Session): void {
    if (session.id === null || this.downloadingId() !== null) {
      return;
    }

    this.downloadingId.set(session.id);

    this.sessionsService.export(session.id).subscribe({
      next: response => {
        const blob = response.body;

        if (!blob || blob.size === 0) {
          this.downloadingId.set(null);
          alert('Le fichier exporté est vide.');
          return;
        }

        const disposition = response.headers.get('Content-Disposition');

        const filename =
          disposition?.match(/filename\*=UTF-8''([^;]+)/i)?.[1] ??
          disposition?.match(/filename="?([^";]+)"?/i)?.[1] ??
          `seance-${session.id}`;

        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');

        link.href = url;
        link.download = decodeURIComponent(filename);
        document.body.appendChild(link);
        link.click();
        link.remove();

        setTimeout(() => URL.revokeObjectURL(url), 1000);

        this.downloadingId.set(null);
      },

      error: error => {
        console.error('Erreur export :', error);
        this.downloadingId.set(null);
        alert("Impossible de télécharger la séance.");
      }
    });
  }

  duplicateSession(session: Session): void {
    if (session.id === null || this.duplicatingId() !== null) {
      return;
    }

    this.duplicatingId.set(session.id);
    this.error.set(null);

    this.sessionsService.clone(session.id).subscribe({
      next: (duplicate) => {
        this.sessions.update(items => [duplicate, ...items]);
        this.duplicatingId.set(null);
      },

      error: (error) => {
        console.error('Erreur duplication :', error);
        this.duplicatingId.set(null);
        alert('Impossible de dupliquer cette séance.');
      }
    });
  }
}
