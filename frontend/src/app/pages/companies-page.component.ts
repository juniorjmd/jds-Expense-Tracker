import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { Company, User } from '../models';
import { ApiRequestError } from '../services/api.service';
import { AuthService } from '../services/auth.service';
import { StorageService } from '../services/storage.service';
import { ModalShellComponent } from '../modalsComponent/modal-shell.component';

@Component({
  selector: 'app-companies-page',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink, ModalShellComponent],
  template: `
    <div class="shell" *ngIf="auth.can('manage-companies'); else blocked">
      <header class="topbar">
        <div>
          <a routerLink="/" class="back">Volver</a>
          <h1>Empresas</h1>
          <p class="muted">Mantenimiento exclusivo para superusuario.</p>
        </div>
        <div class="actions">
          <button class="btn" type="button" (click)="openCompanyModal()">Nueva empresa</button>
        </div>
      </header>

      <section class="panel">
        <div class="panel-head"><h2>Empresas creadas</h2></div>
        <p *ngIf="errorMessage" class="feedback error">{{ errorMessage }}</p>
        <p *ngIf="successMessage" class="feedback success">{{ successMessage }}</p>
        <div class="company-grid">
          <article class="company-card" *ngFor="let item of companies">
            <div>
              <strong>{{ item.name }}</strong>
              <p class="muted">{{ item.description || 'Sin descripcion' }}</p>
            </div>
            <div class="stats">
              <div>
                <span>Establecimientos</span>
                <strong>{{ item.establishmentsCount ?? 0 }}</strong>
              </div>
              <div>
                <span>Usuarios</span>
                <strong>{{ item.usersCount ?? 0 }}</strong>
              </div>
            </div>
            <div class="pill-row">
              <span class="pill">{{ item.planName || 'Plan base' }}</span>
              <span class="pill alt">{{ item.subscriptionStatus || 'Sin estado' }}</span>
              <span class="pill soft">{{ item.currencyCode || 'COP' }}</span>
            </div>
            <div class="meta">
              <a class="btn small" [routerLink]="['/empresas', item.id]">Entrar al detalle</a>
            </div>
          </article>
        </div>
      </section>
    </div>

    <ng-template #blocked>
      <div class="shell"><section class="panel"><h1>Sin acceso</h1><p class="muted">Solo el superusuario puede administrar empresas.</p></section></div>
    </ng-template>

      <app-modal-shell *ngIf="showAdminModal" width="560px" [elevated]="true" labelledBy="admin-modal-title" (closed)="closeAdminModal()">
        <div class="modal-head">
          <div>
            <h2 id="admin-modal-title">Nuevo usuario administrador</h2>
            <p class="muted">Este usuario se creara junto con la empresa y quedara asociado de inmediato.</p>
          </div>
          <button class="icon-btn" type="button" (click)="closeAdminModal()" aria-label="Cerrar">×</button>
        </div>

        <div class="form-grid">
          <label><span>Nombre</span><input [(ngModel)]="draftAdminName"></label>
          <label><span>Email</span><input [(ngModel)]="draftAdminEmail" type="email"></label>
          <label class="full"><span>Contrasena</span><input [(ngModel)]="draftAdminPassword" type="password"></label>
        </div>

        <div class="actions">
          <button class="btn" type="button" (click)="applyAdminDraft()">Guardar usuario</button>
          <button class="btn ghost" type="button" (click)="closeAdminModal()">Cancelar</button>
        </div>
      </app-modal-shell>

      <app-modal-shell *ngIf="showCompanyModal" width="860px" labelledBy="company-modal-title" (closed)="closeCompanyModal()">
          <div class="modal-head">
            <div>
              <h2 id="company-modal-title">Nueva empresa</h2>
              <p class="muted">Crea la empresa sin salir del listado y define de inmediato su administrador inicial.</p>
            </div>
            <button class="icon-btn" type="button" (click)="closeCompanyModal()" aria-label="Cerrar">×</button>
          </div>

          <div class="form-grid">
            <label><span>Empresa</span><input [(ngModel)]="name"></label>
            <label><span>Descripcion</span><input [(ngModel)]="description"></label>
            <div class="full admin-panel">
              <div>
                <span class="label-title">Usuario administrador</span>
                <p class="muted">
                  Puedes elegir un administrador ya creado o crearlo en este momento para dejarlo asociado desde el arranque.
                </p>
              </div>
              <div class="actions">
                <button class="btn ghost" type="button" (click)="openAdminModal()">
                  {{ hasAdminDraft() ? 'Editar administrador nuevo' : 'Agregar administrador' }}
                </button>
              </div>
              <div class="full selector-block">
                <span class="selector-title">Administradores disponibles</span>
                <p *ngIf="!adminCandidates.length" class="muted">No hay usuarios administradores disponibles todavia. Puedes crearlo ahora desde esta misma pantalla.</p>
                <div *ngIf="adminCandidates.length" class="candidate-list">
                  <button
                    *ngFor="let item of adminCandidates"
                    class="candidate-card"
                    type="button"
                    [class.selected]="item.id === selectedAdminUserId"
                    (click)="selectExistingAdmin(item.id)"
                  >
                    <strong>{{ item.name }}</strong>
                    <span>{{ item.email }}</span>
                    <small>Administrador</small>
                  </button>
                </div>
              </div>
              <div *ngIf="selectedAdminUserId" class="admin-summary">
                <strong>{{ selectedAdminLabel() }}</strong>
                <small>Este usuario quedara asignado como administrador de la nueva empresa sin perder sus otras empresas.</small>
              </div>
              <div *ngIf="hasAdminDraft()" class="admin-summary">
                <strong>{{ adminName }}</strong>
                <span>{{ adminEmail }}</span>
                <small>Administrador inicial listo para asociarse a la empresa.</small>
              </div>
            </div>
          </div>

          <p *ngIf="modalErrorMessage" class="feedback error">{{ modalErrorMessage }}</p>

          <div class="actions">
            <button class="btn" type="button" (click)="save()">Crear empresa</button>
            <button class="btn ghost" type="button" (click)="closeCompanyModal()">Cancelar</button>
          </div>
      </app-modal-shell>
  `,
  styles: [`
    .shell { padding:32px; display:grid; gap:24px; }
    .topbar, .panel { background:var(--surface); border:1px solid var(--surface-border); border-radius:28px; box-shadow:var(--shadow-card); backdrop-filter: blur(14px); }
    .topbar, .panel, .company-card { padding:24px; }
    .topbar { background:linear-gradient(135deg, rgba(15,23,42,.97), rgba(30,58,95,.94) 48%, rgba(47,110,165,.90)); color:#fff; position:relative; overflow:hidden; }
    .topbar::after { content:""; position:absolute; width:220px; height:220px; right:-60px; bottom:-110px; border-radius:999px; background:radial-gradient(circle, rgba(244,162,97,.30), transparent 70%); pointer-events:none; }
    .panel { display:grid; gap:18px; background:var(--surface-strong); }
    .form-grid { display:grid; gap:14px; grid-template-columns: repeat(2, minmax(0,1fr)); }
    .full { grid-column:1 / -1; }
    .actions, .meta { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
    .company-grid { display:grid; gap:16px; grid-template-columns:repeat(auto-fit, minmax(320px, 420px)); align-items:start; }
    label { display:grid; gap:8px; }
    input { width:100%; border:1px solid rgba(71, 85, 105, .18); border-radius:18px; padding:14px 16px; font:inherit; background:linear-gradient(180deg, #ffffff, #f8fafc); }
    .btn { border:0; border-radius:999px; padding:12px 18px; background:linear-gradient(135deg, #0f172a, #2f6ea5); color:#fff; cursor:pointer; font-weight:700; text-decoration:none; box-shadow:0 14px 28px rgba(15,23,42,.18); }
    .btn.small { padding:10px 14px; font-size:13px; }
    .btn.ghost { background:linear-gradient(135deg, rgba(15,23,42,.05), rgba(106,166,217,.16)); color:#1e3a5f; border:1px solid rgba(71, 85, 105, .12); box-shadow:none; }
    .admin-panel { display:grid; gap:12px; padding:18px; border-radius:22px; background:linear-gradient(135deg, rgba(15,23,42,.04), rgba(244,162,97,.10)); border:1px solid rgba(71, 85, 105, .10); }
    .label-title { display:block; font-weight:700; color:#22314d; margin-bottom:6px; }
    .selector-block { display:grid; gap:10px; }
    .selector-title { font-weight:700; color:#22314d; }
    .candidate-list { display:grid; gap:10px; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); }
    .candidate-card { display:grid; gap:4px; padding:14px 16px; text-align:left; border:1px solid rgba(71, 85, 105, .12); border-radius:18px; background:rgba(255,255,255,.82); cursor:pointer; color:#22314d; box-shadow:0 8px 20px rgba(15,23,42,.06); }
    .candidate-card strong { font-size:15px; }
    .candidate-card span, .candidate-card small { color:var(--muted); }
    .candidate-card.selected { background:linear-gradient(135deg, rgba(15,23,42,.94), rgba(47,110,165,.92)); border-color:transparent; box-shadow:0 14px 28px rgba(15,23,42,.18); }
    .candidate-card.selected strong, .candidate-card.selected span, .candidate-card.selected small { color:#fff; }
    .admin-summary { display:grid; gap:4px; padding:14px 16px; border-radius:18px; background:rgba(255,255,255,.78); border:1px solid rgba(71, 85, 105, .08); }
    .admin-summary strong { color:var(--ink); }
    .admin-summary span, .admin-summary small { color:var(--muted); }
    .feedback { margin:0; font-weight:700; }
    .feedback.error { color:var(--danger); }
    .feedback.success { color:var(--success); }
    .company-card { display:grid; gap:18px; border-radius:24px; background:linear-gradient(180deg, rgba(255,255,255,.98), rgba(241,245,249,.90)); border:1px solid rgba(71, 85, 105, .12); box-shadow:var(--shadow-card); }
    .stats { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:12px; padding:14px 16px; border-radius:18px; background:linear-gradient(135deg, rgba(15,23,42,.04), rgba(244,162,97,.10)); border:1px solid rgba(71, 85, 105, .10); }
    .stats div { display:grid; gap:6px; }
    .stats span { color:var(--muted); font-size:13px; }
    .stats strong { font-size:28px; letter-spacing:-.04em; color:var(--ink); }
    .pill-row { display:flex; gap:8px; flex-wrap:wrap; }
    .pill { display:inline-flex; align-items:center; padding:8px 12px; border-radius:999px; background:linear-gradient(135deg, rgba(47,110,165,.12), rgba(106,166,217,.24)); color:#24466b; font-size:12px; font-weight:700; }
    .pill.alt { background:linear-gradient(135deg, rgba(244,162,97,.16), rgba(255,255,255,.55)); color:#84522a; }
    .pill.soft { background:linear-gradient(135deg, rgba(15,23,42,.05), rgba(241,245,249,.92)); color:#334155; }
    .back { text-decoration:none; color:inherit; font-weight:700; }
    .muted, .meta span { color:var(--muted); }
    .modal-head { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; }
    .modal-head h2, .modal-head p { margin:0; }
    .icon-btn { width:38px; height:38px; border:0; border-radius:999px; background:rgba(15,23,42,.08); color:#1e3a5f; cursor:pointer; font-size:24px; line-height:1; }
    @media (max-width: 768px) { .shell { padding:18px; } .form-grid { grid-template-columns:1fr; } .company-grid { grid-template-columns:1fr; } .stats { grid-template-columns:1fr; } .modal-card { padding:20px; } }
  `],
})
export class CompaniesPageComponent implements OnInit {
  companies: Company[] = [];
  adminCandidates: User[] = [];
  name = '';
  description = '';
  selectedAdminUserId = '';
  adminName = '';
  adminEmail = '';
  adminPassword = '';
  draftAdminName = '';
  draftAdminEmail = '';
  draftAdminPassword = '';
  showAdminModal = false;
  showCompanyModal = false;
  errorMessage = '';
  successMessage = '';
  modalErrorMessage = '';

  constructor(public auth: AuthService, private readonly storage: StorageService, private readonly router: Router) {
    if (!this.auth.can('manage-companies')) {
      void this.router.navigate(['/']);
    }
  }

  async ngOnInit(): Promise<void> {
    await this.refresh();
  }

  async refresh(): Promise<void> {
    this.errorMessage = '';

    try {
      this.companies = await this.storage.getCompanies();
      let users: User[] = [];

      try {
        users = await this.auth.getUsers();
      } catch {
        users = [];
      }

      this.adminCandidates = users.filter((item) => item.role === 'administrador');
    } catch (error) {
      this.errorMessage = this.describeError(error);
    }
  }

  hasAdminDraft(): boolean {
    return !!(this.adminName.trim() && this.adminEmail.trim() && this.adminPassword.trim());
  }

  openCompanyModal(): void {
    this.resetCompanyForm();
    this.showCompanyModal = true;
  }

  closeCompanyModal(): void {
    this.showCompanyModal = false;
    this.resetCompanyForm();
  }

  openAdminModal(): void {
    this.draftAdminName = this.adminName;
    this.draftAdminEmail = this.adminEmail;
    this.draftAdminPassword = this.adminPassword;
    this.showAdminModal = true;
  }

  closeAdminModal(): void {
    this.showAdminModal = false;
  }

  selectedAdminLabel(): string {
    const admin = this.adminCandidates.find((item) => item.id === this.selectedAdminUserId);
    return admin ? `${admin.name} · ${admin.email}` : '';
  }

  selectExistingAdmin(userId: string): void {
    this.selectedAdminUserId = userId;
    this.adminName = '';
    this.adminEmail = '';
    this.adminPassword = '';
    this.draftAdminName = '';
    this.draftAdminEmail = '';
    this.draftAdminPassword = '';
  }

  applyAdminDraft(): void {
    if (!this.draftAdminName.trim() || !this.draftAdminEmail.trim() || !this.draftAdminPassword.trim()) {
      return;
    }

    this.adminName = this.draftAdminName.trim();
    this.adminEmail = this.draftAdminEmail.trim().toLowerCase();
    this.adminPassword = this.draftAdminPassword;
    this.selectedAdminUserId = '';
    this.showAdminModal = false;
  }

  async save(): Promise<void> {
    if (!this.name.trim()) {
      this.modalErrorMessage = 'VALIDATION_ERROR: El nombre de la empresa es obligatorio.';
      return;
    }

    if (!this.selectedAdminUserId && (!this.adminName.trim() || !this.adminEmail.trim() || !this.adminPassword.trim())) {
      this.modalErrorMessage = 'VALIDATION_ERROR: Debes elegir un administrador existente o completar el nuevo administrador.';
      return;
    }

    this.modalErrorMessage = '';
    this.errorMessage = '';
    this.successMessage = '';

    try {
      await this.storage.saveCompany(this.selectedAdminUserId
        ? {
            name: this.name,
            description: this.description,
            existingAdminUserId: this.selectedAdminUserId,
          }
        : {
            name: this.name,
            description: this.description,
            adminName: this.adminName,
            adminEmail: this.adminEmail,
            adminPassword: this.adminPassword,
          });

      this.successMessage = 'Empresa creada correctamente.';
      this.closeCompanyModal();
      await this.refresh();
    } catch (error) {
      this.modalErrorMessage = this.describeError(error);
    }
  }

  private resetCompanyForm(): void {
    this.name = '';
    this.description = '';
    this.selectedAdminUserId = '';
    this.adminName = '';
    this.adminEmail = '';
    this.adminPassword = '';
    this.draftAdminName = '';
    this.draftAdminEmail = '';
    this.draftAdminPassword = '';
    this.showAdminModal = false;
    this.modalErrorMessage = '';
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
