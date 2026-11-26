import { Injectable, inject, computed } from '@angular/core';
import { Auth } from './auth';

export enum Role {
    ADMIN = 'ROLE_ADMIN',
    CREATOR = 'ROLE_CREATOR',
    COACH = 'ROLE_COACH',
    TRAINEE = 'ROLE_TRAINEE'
}

@Injectable({
    providedIn: 'root'
})
export class Permissions {

    private readonly auth = inject(Auth);

    private readonly hierarchy: Record<Role, Role[]> = {
        [Role.ADMIN]: [
            Role.CREATOR,
            Role.COACH,
            Role.TRAINEE
        ],
        [Role.CREATOR]: [
            Role.COACH,
            Role.TRAINEE
        ],
        [Role.COACH]: [
            Role.TRAINEE
        ],
        [Role.TRAINEE]: []
    };

    readonly roles = computed(() => {
        const roles = new Set<string>();

        for (const role of this.auth.roles()) {
            roles.add(role);

            for (const inherited of this.hierarchy[role as Role] ?? []) {
                roles.add(inherited);
            }
        }

        return roles;
    });

    hasRole(role: Role): boolean {
        return this.roles().has(role);
    }

    canListSessions(): boolean {
        return this.hasRole(Role.COACH);
    }

    canViewSession(): boolean {
        return this.hasRole(Role.TRAINEE);
    }

    canCreateSession(): boolean {
        return this.hasRole(Role.CREATOR);
    }

    canEditSession(): boolean {
        return this.hasRole(Role.CREATOR);
    }

    canDuplicateSession(): boolean {
        return this.hasRole(Role.CREATOR);
    }

    canDeleteSession(): boolean {
        return this.hasRole(Role.CREATOR);
    }

    canExportSession(): boolean {
        return this.hasRole(Role.COACH);
    }
}
