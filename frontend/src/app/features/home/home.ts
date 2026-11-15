
import { Component, inject, OnInit, signal } from '@angular/core';
import { Stats, StatsService } from '../../core/api/stats';

@Component({
  selector: 'app-home',
  standalone: true,
  templateUrl: './home.html',
  styleUrl: './home.css'
})
export class Home implements OnInit {
  private readonly statsService = inject(StatsService);

  readonly stats = signal<Stats | null>(null);
  readonly loading = signal(true);
  readonly error = signal<string | null>(null);

  ngOnInit(): void {
    this.statsService.getStats().subscribe({
      next: (data) => {
        this.stats.set(data);
        this.loading.set(false);
      },
      error: () => {
        this.error.set('Impossible de récupérer les statistiques.');
        this.loading.set(false);
      }
    });
  }
}
