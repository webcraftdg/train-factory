import { Component, inject, OnInit, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { SessionsService } from '../../../core/api/sessions';
import { Permissions } from '../../../core/auth/permissions';
import { Session } from '../models/session';

@Component({
    selector: 'app-session-view',
    standalone: true,
    imports: [RouterLink],
    templateUrl: './session-view.html',
    styleUrl: './session-view.css'
})
export class SessionView implements OnInit {

    private readonly route = inject(ActivatedRoute);
    private readonly sessionsService = inject(SessionsService);

    readonly permissions = inject(Permissions);
    readonly session = signal<Session | null>(null);
    readonly loading = signal(true);
    readonly error = signal<string | null>(null);

    ngOnInit(): void {
        const id = Number(this.route.snapshot.paramMap.get('id'));

        if (!Number.isSafeInteger(id) || id <= 0) {
            this.error.set('Identifiant de séance invalide.');
            this.loading.set(false);
            return;
        }

        this.sessionsService.getOne(id).subscribe({
            next: session => {
                this.session.set(session);
                this.loading.set(false);
            },
            error: () => {
                this.error.set('Impossible de charger cette séance.');
                this.loading.set(false);
            }
        });
    }
}
