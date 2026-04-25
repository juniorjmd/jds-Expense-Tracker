import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Input, Output } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { User } from '../models';
import { ModalShellComponent } from './modal-shell.component';

export interface CompanyModalPayload {
  name: string;
  description: string;
  existingAdminUserId?: string;
  adminName?: string;
  adminEmail?: string;
  adminPassword?: string;
}

@Component({
  selector: 'app-company-modal',
  standalone: true,
  imports: [CommonModule, FormsModule, ModalShellComponent],
  template: `
    <app-modal-shell width="860px" labelledBy="company-modal-title" (closed)="closed.emit()">
      <div class="modal-head">
        <div>
          <h2 id="company-modal-title">Nueva empresa</h2>
          <p class="muted">Crea la empresa sin salir del listado y define de inmediato su administrador inicial.</p>
        </div>
        <button class="icon-btn" type="button" (click)="closed.emit()" aria-label="Cerrar">×</button>
      </div>

      <div class="form-grid">
        <label><span>Empresa</span><input [(ngModel)]="name"></label>
        <label><span>Descripcion</span><input [(ngModel)]="description"></label>
        <div class="full admin-panel">
          <div>
            <span class="label-title">Usuario administrador</span>
            <p class="muted">Puedes elegir un administrador ya creado o crearlo en este momento para dejarlo asociado desde el arranque.</p>
          </div>
          <div class="actions">
            <button class="btn ghost" type="button" (click)="adminRequested.emit()">
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

      <p *ngIf="errorMessage" class="feedback error">{{ errorMessage }}</p>

      <div class="actions">
        <button class="btn" type="button" (click)="save()">Crear empresa</button>
        <button class="btn ghost" type="button" (click)="closed.emit()">Cancelar</button>
      </div>
    </app-modal-shell>
  `,
  styles: [`
    .modal-head { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; }
    .modal-head h2, .modal-head p { margin:0; }
    .form-grid { display:grid; gap:14px; grid-template-columns: repeat(2, minmax(0,1fr)); }
    .full { grid-column:1 / -1; }
    label { display:grid; gap:8px; color:#22314d; font-weight:700; }
    input { width:100%; border:1px solid rgba(71, 85, 105, .18); border-radius:18px; padding:14px 16px; font:inherit; background:linear-gradient(180deg, #ffffff, #f8fafc); }
    .actions { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
    .btn { border:0; border-radius:999px; padding:12px 18px; background:linear-gradient(135deg, #0f172a, #2f6ea5); color:#fff; cursor:pointer; font-weight:700; box-shadow:0 14px 28px rgba(15,23,42,.18); }
    .btn.ghost { background:linear-gradient(135deg, rgba(15,23,42,.05), rgba(106,166,217,.16)); color:#1e3a5f; border:1px solid rgba(71, 85, 105, .12); box-shadow:none; }
    .admin-panel { display:grid; gap:12px; padding:18px; border-radius:22px; background:linear-gradient(135deg, rgba(15,23,42,.04), rgba(244,162,97,.10)); border:1px solid rgba(71, 85, 105, .10); }
    .label-title, .selector-title { display:block; font-weight:700; color:#22314d; }
    .candidate-list { display:grid; gap:10px; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); }
    .candidate-card { display:grid; gap:4px; padding:14px 16px; text-align:left; border:1px solid rgba(71, 85, 105, .12); border-radius:18px; background:rgba(255,255,255,.82); cursor:pointer; color:#22314d; box-shadow:0 8px 20px rgba(15,23,42,.06); }
    .candidate-card span, .candidate-card small { color:var(--muted); }
    .candidate-card.selected { background:linear-gradient(135deg, rgba(15,23,42,.94), rgba(47,110,165,.92)); border-color:transparent; box-shadow:0 14px 28px rgba(15,23,42,.18); }
    .candidate-card.selected strong, .candidate-card.selected span, .candidate-card.selected small { color:#fff; }
    .admin-summary { display:grid; gap:4px; padding:14px 16px; border-radius:18px; background:rgba(255,255,255,.78); border:1px solid rgba(71, 85, 105, .08); }
    .admin-summary span, .admin-summary small, .muted { color:var(--muted); }
    .feedback { margin:0; font-weight:700; }
    .feedback.error { color:var(--danger); }
    .icon-btn { width:38px; height:38px; border:0; border-radius:999px; background:rgba(15,23,42,.08); color:#1e3a5f; cursor:pointer; font-size:24px; line-height:1; }
    @media (max-width: 768px) { .form-grid { grid-template-columns:1fr; } }
  `],
})
export class CompanyModalComponent {
  @Input() adminCandidates: User[] = [];
  @Input() adminName = '';
  @Input() adminEmail = '';
  @Input() adminPassword = '';
  @Input() errorMessage = '';
  @Output() adminRequested = new EventEmitter<void>();
  @Output() saved = new EventEmitter<CompanyModalPayload>();
  @Output() existingAdminSelected = new EventEmitter<void>();
  @Output() closed = new EventEmitter<void>();

  name = '';
  description = '';
  selectedAdminUserId = '';

  hasAdminDraft(): boolean {
    return !!(this.adminName.trim() && this.adminEmail.trim() && this.adminPassword.trim());
  }

  selectedAdminLabel(): string {
    const admin = this.adminCandidates.find((item) => item.id === this.selectedAdminUserId);
    return admin ? `${admin.name} · ${admin.email}` : '';
  }

  selectExistingAdmin(userId: string): void {
    this.selectedAdminUserId = userId;
    this.existingAdminSelected.emit();
  }

  save(): void {
    this.saved.emit(this.selectedAdminUserId
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
  }
}
