import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { Permissions, Role } from './permissions';

export function permissionGuard(role: Role): CanActivateFn {

    return () => {
        const permissions = inject(Permissions);
        const router = inject(Router);

        return permissions.hasRole(role)
            ? true
            : router.createUrlTree(['/tableau-de-bord']);
    };
}
