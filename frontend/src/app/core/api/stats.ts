import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Api } from './api';

export interface Stats {
  nbSessions: number;
  nbExercises: number;
  nbBlocks: number;
}

@Injectable({
  providedIn: 'root'
})
export class StatsService {
  private readonly http = inject(HttpClient);

  getStats(): Observable<Stats> {
    return this.http.get<Stats>(Api.STATS);
  }
}
