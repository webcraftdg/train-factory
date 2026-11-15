
import { Component, inject, OnInit, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import {
  SessionsService,
  TrainingSession
} from '../../core/api/sessions';

@Component({
  selector: 'app-sessions',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './sessions.html',
  styleUrl: './sessions.css'
})
export class Sessions implements OnInit {
  private readonly sessionsService = inject(SessionsService);

  readonly sessions = signal<TrainingSession[]>([]);
  readonly loading = signal(true);
  readonly deletingId = signal<number | null>(null);
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

  deleteSession(session: TrainingSession): void {
    if (!confirm(`Supprimer la séance « ${session.name} » ?`)) {
      return;
    }

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
