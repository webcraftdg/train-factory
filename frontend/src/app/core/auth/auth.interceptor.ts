
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import {
  HttpErrorResponse,
  HttpInterceptorFn
} from '@angular/common/http';

import { catchError, switchMap, throwError, shareReplay, finalize, Observable } from 'rxjs';

import { Auth } from './auth';
import { Api } from '../api/api';

let refreshRequest$: Observable<{ token: string }> | null = null;

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const auth = inject(Auth);
  const router = inject(Router);

  if (!req.url.startsWith('/api/')) {
    return next(req);
  }

  // Ne pas intercepter le login ni le renouvellement
  if (req.url === Api.AUTHENTICATION || req.url === Api.REFRESH_TOKEN) {
    return next(req);
  }

  const token = auth.getToken();

  const authenticatedRequest = token
    ? req.clone({
        setHeaders: {
          Authorization: `Bearer ${token}`
        }
      })
    : req;

  return next(authenticatedRequest).pipe(
    catchError((error: HttpErrorResponse) => {
      if (error.status !== 401) {
        return throwError(() => error);
      }

      // Un seul renouvellement, même si plusieurs appels échouent
      if (!refreshRequest$) {
        refreshRequest$ = auth.refreshToken().pipe(
          shareReplay(1),
          finalize(() => {
            refreshRequest$ = null;
          })
        );
      }

      return refreshRequest$.pipe(
        switchMap(response => {
          const retryRequest = req.clone({
            setHeaders: {
              Authorization: `Bearer ${response.token}`
            }
          });

          return next(retryRequest);
        }),
        catchError(refreshError => {
          auth.logout();
          void router.navigateByUrl('/login');
          return throwError(() => refreshError);
        })
      );
    })
  );
};
