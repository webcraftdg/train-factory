import { Routes } from '@angular/router';
import { authGuard } from './core/auth/auth-guard';
import { permissionGuard } from './core/auth/permission-guard';
import { Role } from './core/auth/permissions';

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
  canActivate: [
      authGuard,
      permissionGuard(Role.COACH)
  ],
  loadComponent: () =>
    import('./features/sessions/sessions').then(m => m.Sessions)
},
{
  path: 'seances/creer',
  canActivate: [
    authGuard,
    permissionGuard(Role.CREATOR)
  ],
  loadComponent: () =>
    import('./features/sessions/session-editor/session-editor')
      .then(m => m.SessionEditor)
},
{
  path: 'seances/:id/modifier',
  canActivate: [
    authGuard,
    permissionGuard(Role.CREATOR)],
  loadComponent: () =>
    import('./features/sessions/session-editor/session-editor')
      .then(m => m.SessionEditor)
},
{
    path: 'seances/:id',
    canActivate: [
      authGuard,
      permissionGuard(Role.TRAINEE)
    ],
    loadComponent: () =>
        import('./features/sessions/session-view/session-view')
            .then(m => m.SessionView)
},
  {
    path: '**',
    redirectTo: 'tableau-de-bord'
  }
];
