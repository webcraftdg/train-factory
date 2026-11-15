import { inject, Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Api } from './api';

export interface TrainingSession {
  id: number;
  name: string;
  description?: string | null;
}

@Injectable({
  providedIn: 'root'
})
export class SessionsService {
  private readonly http = inject(HttpClient);

  getAll(): Observable<TrainingSession[]> {
    return this.http.get<TrainingSession[]>(Api.SESSIONS);
  }

  getOne(id: number): Observable<TrainingSession> {
    return this.http.get<TrainingSession>(`${Api.SESSIONS}/${id}`);
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${Api.SESSIONS}/${id}`);
  }
}
