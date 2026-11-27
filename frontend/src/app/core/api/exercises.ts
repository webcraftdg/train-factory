import { inject, Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { Api } from './api';
import { Exercise } from '../../features/sessions/models/session';


@Injectable({
  providedIn: 'root'
})
export class ExerciseService {
  private readonly http = inject(HttpClient);
  private readonly _exercises = signal<Exercise[]>([]);
  readonly exercises = this._exercises.asReadonly();

  getAll(): Observable<Exercise[]> {
    return this.http.get<Exercise[]>(Api.EXERCISES);
  }

  load() : Observable<Exercise[]> {
    return this.getAll().pipe(
      tap(response => {
          this._exercises.set(response);
      })
    );
  }

  create(exercise: Exercise): Observable<Exercise> {
    return this.http.post<Exercise>(Api.EXERCISES, exercise).pipe(
      tap(response => this._exercises.update(exercises => [...exercises, response]))
    );
  }
}
