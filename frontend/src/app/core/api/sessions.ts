import { inject, Injectable } from '@angular/core';
import { HttpClient, HttpResponse } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Api } from './api';
import { Session } from '../../features/sessions/models/session';


@Injectable({
  providedIn: 'root'
})
export class SessionsService {
  private readonly http = inject(HttpClient);

  getAll(): Observable<Session[]> {
    return this.http.get<Session[]>(Api.SESSIONS);
  }

  getOne(id: number): Observable<Session> {
    return this.http.get<Session>(`${Api.SESSIONS}/${id}`);
  }

  create(session: Session): Observable<Session> {
    return this.http.post<Session>(Api.SESSIONS, session);
  }

  update(id: number, session: Session): Observable<Session> {
    return this.http.patch<Session>(
      `${Api.SESSIONS}/${id}`,
      session
    );
  }
  clone(id: number): Observable<Session> {
  return this.http.post<Session>(
    `${Api.SESSIONS}/${id}/clone`,
    {}
  );
}

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${Api.SESSIONS}/${id}`);
  }

  export(id: number): Observable<HttpResponse<Blob>> {
  return this.http.get(`${Api.SESSIONS_EXPORT}/${id}/excel`, {
    observe: 'response',
    responseType: 'blob'
  });
}
}
