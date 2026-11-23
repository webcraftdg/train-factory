import { inject, Injectable } from '@angular/core';
import { HttpClient, HttpResponse } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Api } from './api';
import { Exercise, Session } from '../../features/sessions/models/session';


@Injectable({
  providedIn: 'root'
})
export class ExerciseService {
  private readonly http = inject(HttpClient);

  getAll(): Observable<Exercise[]> {
    return this.http.get<Exercise[]>(Api.EXERCISES);
  }
}
