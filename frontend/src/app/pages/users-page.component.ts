import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { Establishment, User, UserRole } from '../models';
import { AuthService } from '../services/auth.service';
import { StorageService } from '../services/storage.service';

@Component({
  selector: 'app-users-page',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  template: `
    <div class="shell" *ngIf="auth.can('manage-users'); else blocked">
      <header class="topbar">
        <div>
          <a routerLink="/" class="back">Volver</a>
          <h1>Usuarios y permisos</h1>
          <p class="muted">Vista administrativa alineada con el flujo del Figma.</p>
        </div>
      </header>

      <section class="panel">
        <div class="panel-head"><h2>{{ editingId ? 'Editar usuario' : 'Nuevo usuario' }}</h2></div>
        <div class="form-grid">
          <label><span>Nombre</span><input [(ngModel)]="name"></label>
          <label><span>Email</span><input [(ngModel)]="email" type="email"></label>
          <label><span>Contrasena</span><input [(ngModel)]="password" type="password"></label>
          <label><span>Rol</span><select [(ngModel)]="role"><option value="administrador">Administrador</option><option value="editor">Editor</option><option value="visualizador">Visualizador</option></select></label>
          <label class="full" *ngIf="role !== 'administrador'">
            <span>Establecimientos asignados</span>
            <div class="chips">
              <button class="chip" type="button" *ngFor="let item of establishments" (click)="toggleEstablishment(item.id)" [class.active]="assigned.includes(item.id)">
                {{ item.name }}
              </button>
            </div>
          </label>
        </div>
        <div class="actions">
          <button class="btn" type="button" (click)="saveUser()">{{ editingId ? 'Actualizar' : 'Guardar' }}</button>
          <button *ngIf="editingId" class="btn ghost" type="button" (click)="resetForm()">Cancelar</button>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Usuarios actuales</h2></div>
        <div class="stack">
          <article class="row-card" *ngFor="let item of users">
            <div>
              <strong>{{ item.name }}</strong>
              <p class="muted">{{ item.email }}</p>
              <small>{{ item.role }}</small>
            </div>
            <div class="actions">
              <button class="btn ghost" type="button" (click)="editUser(item.id)">Editar</button>
              <button class="btn ghost" type="button" (click)="deleteUser(item.id)" [disabled]="item.email === 'admin@sistema.com'">Eliminar</button>
            </div>
          </article>
        </div>
      </section>
    </div>

    <ng-template #blocked>
      <div class="shell"><section class="panel"><h1>Sin acceso</h1><p class="muted">Solo administradores pueden ver esta pantalla.</p></section></div>
    </ng-template>
  `,
  styles: [`
    .shell { padding:32px; display:grid; gap:24px; }
    .topbar, .panel { background:#fff; border-radius:24px; box-shadow:0 18px 50px rgba(18,30,61,.08); }
    .topbar, .panel, .row-card { padding:24px; }
    .panel { display:grid; gap:18px; }
    .form-grid { display:grid; gap:14px; grid-template-columns: repeat(2, minmax(0,1fr)); }
    .full { grid-column:1 / -1; }
    label { display:grid; gap:8px; }
    input, select { width:100%; border:1px solid #d8dfec; border-radius:16px; padding:14px 16px; font:inherit; background:#f9fbff; }
    .chips, .actions, .stack { display:flex; gap:10px; flex-wrap:wrap; }
    .stack { display:grid; }
    .chip, .btn { border:0; border-radius:999px; padding:10px 16px; cursor:pointer; font:inherit; }
    .chip { background:#eef3ff; color:#32518f; }
    .chip.active, .btn { background:#14213d; color:#fff; }
    .btn.ghost { background:#eef3ff; color:#23407a; }
    .row-card { display:flex; justify-content:space-between; gap:16px; border-radius:20px; background:#f7f9fd; }
    .back { text-decoration:none; color:#42629d; font-weight:700; }
    .muted, small { color:#66728a; }
    @media (max-width: 768px) { .shell { padding:18px; } .form-grid { grid-template-columns:1fr; } .row-card { flex-direction:column; } }
  `],
})
export class UsersPageComponent implements OnInit {
  users: User[] = [];
  establishments: Establishment[] = [];
  editingId = '';
  name = '';
  email = '';
  password = '';
  role: UserRole = 'visualizador';
  assigned: string[] = [];

  constructor(public auth: AuthService, private readonly storage: StorageService, private readonly router: Router) {
    if (!this.auth.can('manage-users')) {
      void this.router.navigate(['/']);
    }
  }

  async ngOnInit(): Promise<void> {
    await this.refresh();
  }

  async refresh(): Promise<void> {
    this.users = await this.auth.getUsers();
    this.establishments = await this.storage.getEstablishments();
  }

  toggleEstablishment(id: string): void {
    this.assigned = this.assigned.includes(id) ? this.assigned.filter((item) => item !== id) : [...this.assigned, id];
  }

  async saveUser(): Promise<void> {
    if (!this.name.trim() || !this.email.trim() || (!this.editingId && !this.password.trim())) {
      return;
    }

    await this.auth.saveUser({
      id: this.editingId || undefined,
      name: this.name,
      email: this.email,
      password: this.password || undefined,
      role: this.role,
      assignedEstablishments: this.assigned,
    });

    await this.refresh();
    this.resetForm();
  }

  editUser(id: string): void {
    const item = this.users.find((user) => user.id === id);
    if (!item) {
      return;
    }

    this.editingId = item.id;
    this.name = item.name;
    this.email = item.email;
    this.password = '';
    this.role = item.role;
    this.assigned = [...item.assignedEstablishments];
  }

  async deleteUser(id: string): Promise<void> {
    await this.auth.deleteUser(id);
    await this.refresh();
  }

  resetForm(): void {
    this.editingId = '';
    this.name = '';
    this.email = '';
    this.password = '';
    this.role = 'visualizador';
    this.assigned = [];
  }
}
