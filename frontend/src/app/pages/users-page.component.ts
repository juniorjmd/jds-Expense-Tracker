import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { Company, Establishment, User, UserRole } from '../models';
import { ApiRequestError } from '../services/api.service';
import { AuthService } from '../services/auth.service';
import { StorageService } from '../services/storage.service';
import { ModalShellComponent } from '../modalsComponent/modal-shell.component';

@Component({
  selector: 'app-users-page',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink, ModalShellComponent],
  template: `
    <div class="shell" *ngIf="auth.can('manage-users'); else blocked">
      <header class="topbar">
        <div>
          <a routerLink="/" class="back">Volver</a>
          <h1>Usuarios y permisos</h1>
          <p class="muted">Administra accesos sin perder el contexto de la lista principal.</p>
        </div>
        <div class="actions">
          <button class="btn" type="button" (click)="openCreateModal()">Nuevo usuario</button>
        </div>
      </header>

      <section class="panel">
        <div class="panel-head"><h2>Usuarios actuales</h2></div>
        <p *ngIf="errorMessage" class="feedback error">{{ errorMessage }}</p>
        <p *ngIf="successMessage" class="feedback success">{{ successMessage }}</p>
        <div class="stack">
          <article class="row-card" *ngFor="let item of users">
            <div>
              <strong>{{ item.name }}</strong>
              <p class="muted">{{ item.email }}</p>
              <small>{{ item.role }}{{ item.companyName ? ' - ' + item.companyName : '' }}</small>
            </div>
            <div class="actions">
              <button class="btn ghost" type="button" (click)="openEditModal(item.id)">Editar</button>
              <button class="btn ghost" type="button" (click)="deleteUser(item.id)" [disabled]="item.role === 'superusuario'">Eliminar</button>
            </div>
          </article>
        </div>
      </section>

      <app-modal-shell *ngIf="showModal" width="760px" labelledBy="user-modal-title" (closed)="closeModal()">
          <div class="panel-head modal-head">
            <div>
              <h2 id="user-modal-title">{{ editingId ? 'Editar usuario' : 'Nuevo usuario' }}</h2>
              <p class="muted" *ngIf="!editingId">La contrasena inicial se genera automaticamente y se envia al correo del usuario.</p>
              <p class="muted" *ngIf="editingId">Actualiza los datos sin salir de la lista. La contrasena es opcional en edicion.</p>
            </div>
            <button class="icon-btn" type="button" (click)="closeModal()" aria-label="Cerrar">×</button>
          </div>

          <div class="form-grid">
            <label><span>Nombre</span><input [(ngModel)]="name"></label>
            <label><span>Email</span><input [(ngModel)]="email" type="email"></label>
            <label *ngIf="auth.getCurrentUser()?.role === 'superusuario'"><span>Empresa</span><select [(ngModel)]="companyId" (ngModelChange)="filterEstablishmentsByCompany()"><option value="">Seleccione empresa</option><option *ngFor="let item of companies" [value]="item.id">{{ item.name }}</option></select></label>
            <label><span>Rol</span><select [(ngModel)]="role"><option value="administrador">Administrador</option><option value="editor">Editor</option><option value="visualizador">Visualizador</option></select></label>
            <label class="full" *ngIf="editingId">
              <span>Nueva contrasena opcional</span>
              <input [(ngModel)]="password" type="password" placeholder="Solo diligencia si deseas reemplazarla">
            </label>
            <label class="full" *ngIf="role !== 'administrador'">
              <span>Establecimientos asignados</span>
              <div class="chips">
                <button class="chip" type="button" *ngFor="let item of filteredEstablishments" (click)="toggleEstablishment(item.id)" [class.active]="assigned.includes(item.id)">
                  {{ item.name }}
                </button>
              </div>
            </label>
          </div>

          <p *ngIf="modalErrorMessage" class="feedback error">{{ modalErrorMessage }}</p>

          <div class="actions">
            <button class="btn" type="button" (click)="saveUser()">{{ editingId ? 'Actualizar' : 'Crear y enviar acceso' }}</button>
            <button class="btn ghost" type="button" (click)="closeModal()">Cancelar</button>
          </div>
      </app-modal-shell>
    </div>

    <ng-template #blocked>
      <div class="shell"><section class="panel"><h1>Sin acceso</h1><p class="muted">Solo administradores pueden ver esta pantalla.</p></section></div>
    </ng-template>
  `,
  styles: [`
    .shell { padding:32px; display:grid; gap:24px; }
    .topbar, .panel { background:var(--surface); border:1px solid var(--surface-border); border-radius:28px; box-shadow:var(--shadow-card); backdrop-filter: blur(14px); }
    .topbar, .panel, .row-card { padding:24px; }
    .topbar { background:linear-gradient(135deg, rgba(15,23,42,.97), rgba(30,58,95,.94) 48%, rgba(47,110,165,.90)); color:#fff; position:relative; overflow:hidden; }
    .topbar::after { content:""; position:absolute; width:220px; height:220px; right:-60px; bottom:-110px; border-radius:999px; background:radial-gradient(circle, rgba(244,162,97,.30), transparent 70%); pointer-events:none; }
    .panel { display:grid; gap:18px; background:var(--surface-strong); }
    .form-grid { display:grid; gap:14px; grid-template-columns: repeat(2, minmax(0,1fr)); }
    .full { grid-column:1 / -1; }
    label { display:grid; gap:8px; color:#22314d; font-weight:700; }
    input, select { width:100%; border:1px solid rgba(71, 85, 105, .18); border-radius:18px; padding:14px 16px; background:linear-gradient(180deg, #ffffff, #f8fafc); }
    .chips, .actions, .stack { display:flex; gap:10px; flex-wrap:wrap; }
    .stack { display:grid; }
    .chip, .btn { border:0; border-radius:999px; padding:10px 16px; cursor:pointer; font:inherit; }
    .chip { background:linear-gradient(135deg, rgba(15,23,42,.05), rgba(106,166,217,.16)); color:#24466b; border:1px solid rgba(71, 85, 105, .12); }
    .chip.active, .btn { background:linear-gradient(135deg, #0f172a, #2f6ea5); color:#fff; box-shadow:0 14px 28px rgba(15,23,42,.18); }
    .btn.ghost { background:linear-gradient(135deg, rgba(15,23,42,.05), rgba(106,166,217,.16)); color:#1e3a5f; border:1px solid rgba(71, 85, 105, .12); box-shadow:none; }
    .row-card { display:flex; justify-content:space-between; gap:16px; border-radius:20px; background:linear-gradient(135deg, rgba(15,23,42,.04), rgba(244,162,97,.10)); border:1px solid rgba(71, 85, 105, .12); }
    .feedback { margin:0; font-weight:700; }
    .feedback.error { color:var(--danger); }
    .feedback.success { color:var(--success); }
    .modal-head { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; }
    .icon-btn { width:42px; height:42px; border:0; border-radius:999px; background:rgba(15,23,42,.08); color:#24466b; font-size:24px; cursor:pointer; }
    .back { text-decoration:none; color:inherit; font-weight:700; }
    .muted, small { color:var(--muted); }
    @media (max-width: 768px) { .shell { padding:18px; } .form-grid { grid-template-columns:1fr; } .row-card { flex-direction:column; } }
  `],
})
export class UsersPageComponent implements OnInit {
  users: User[] = [];
  companies: Company[] = [];
  establishments: Establishment[] = [];
  filteredEstablishments: Establishment[] = [];
  editingId = '';
  companyId = '';
  name = '';
  email = '';
  password = '';
  role: UserRole = 'visualizador';
  assigned: string[] = [];
  errorMessage = '';
  successMessage = '';
  modalErrorMessage = '';
  showModal = false;

  constructor(public auth: AuthService, private readonly storage: StorageService, private readonly router: Router) {
    if (!this.auth.can('manage-users')) {
      void this.router.navigate(['/']);
    }
  }

  async ngOnInit(): Promise<void> {
    await this.refresh();
  }

  async refresh(): Promise<void> {
    this.errorMessage = '';

    try {
      this.users = await this.auth.getUsers();
      if (this.auth.getCurrentUser()?.role === 'superusuario') {
        this.companies = await this.storage.getCompanies();
      }
      this.establishments = await this.storage.getEstablishments();
      this.filterEstablishmentsByCompany();
    } catch (error) {
      this.errorMessage = this.describeError(error);
    }
  }

  filterEstablishmentsByCompany(): void {
    const currentUser = this.auth.getCurrentUser();
    const activeCompanyId = currentUser?.role === 'superusuario' ? this.companyId : currentUser?.companyId || '';
    this.filteredEstablishments = activeCompanyId
      ? this.establishments.filter((item) => item.companyId === activeCompanyId)
      : this.establishments;
    this.assigned = this.assigned.filter((item) => this.filteredEstablishments.some((establishment) => establishment.id === item));
  }

  toggleEstablishment(id: string): void {
    this.assigned = this.assigned.includes(id) ? this.assigned.filter((item) => item !== id) : [...this.assigned, id];
  }

  async saveUser(): Promise<void> {
    if (!this.name.trim() || !this.email.trim()) {
      this.modalErrorMessage = 'VALIDATION_ERROR: Nombre y email son obligatorios.';
      return;
    }

    this.modalErrorMessage = '';
    this.errorMessage = '';
    this.successMessage = '';

    try {
      await this.auth.saveUser({
        id: this.editingId || undefined,
        companyId: this.auth.getCurrentUser()?.role === 'superusuario' ? this.companyId : this.auth.getCurrentUser()?.companyId || undefined,
        name: this.name,
        email: this.email,
        password: this.password || undefined,
        role: this.role,
        assignedEstablishments: this.assigned,
      });

      this.successMessage = this.editingId
        ? 'Usuario actualizado correctamente.'
        : 'Usuario creado correctamente. La contrasena inicial fue enviada por correo.';
      await this.refresh();
      this.closeModal();
    } catch (error) {
      this.modalErrorMessage = this.describeError(error);
    }
  }

  openCreateModal(): void {
    this.resetForm();
    this.showModal = true;
  }

  openEditModal(id: string): void {
    const item = this.users.find((user) => user.id === id);
    if (!item) {
      return;
    }

    this.editingId = item.id;
    this.name = item.name;
    this.email = item.email;
    this.password = '';
    this.companyId = item.companyId ?? '';
    this.role = item.role;
    this.assigned = [...item.assignedEstablishments];
    this.filterEstablishmentsByCompany();
    this.modalErrorMessage = '';
    this.showModal = true;
  }

  async deleteUser(id: string): Promise<void> {
    this.errorMessage = '';
    this.successMessage = '';

    try {
      await this.auth.deleteUser(id);
      this.successMessage = 'Usuario eliminado correctamente.';
      await this.refresh();
    } catch (error) {
      this.errorMessage = this.describeError(error);
    }
  }

  resetForm(): void {
    this.editingId = '';
    this.companyId = '';
    this.name = '';
    this.email = '';
    this.password = '';
    this.role = 'visualizador';
    this.assigned = [];
    this.modalErrorMessage = '';
  }

  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }

  private describeError(error: unknown): string {
    if (error instanceof ApiRequestError) {
      return `${error.code}: ${error.message}`;
    }

    if (error instanceof Error) {
      return error.message;
    }

    return 'UNEXPECTED_ERROR: Ocurrio un error inesperado.';
  }
}
