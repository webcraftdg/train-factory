import { Routes } from '@angular/router';
import { authGuard } from './core/auth/auth-guard';

export const routes: Routes = [
  {
    path: '',
    redirectTo: 'tableau-de-bord',
    pathMatch: 'full'
  },
  {
    path: 'login',
    loadComponent: () =>
      import('./features/login/login').then(m => m.Login)
  },
  {
    path: 'tableau-de-bord',
    canActivate: [authGuard],
    loadComponent: () =>
      import('./features/home/home').then(m => m.Home)
  },
  {
  path: 'seances',
  canActivate: [authGuard],
  loadComponent: () =>
    import('./features/sessions/sessions').then(m => m.Sessions)
},
{
  path: 'seances/creer',
  canActivate: [authGuard],
  loadComponent: () =>
    import('./features/sessions/session-editor/session-editor')
      .then(m => m.SessionEditor)
},
{
  path: 'seances/:id/modifier',
  canActivate: [authGuard],
  loadComponent: () =>
    import('./features/sessions/session-editor/session-editor')
      .then(m => m.SessionEditor)
},
  {
    path: '**',
    redirectTo: 'tableau-de-bord'
  }
];
