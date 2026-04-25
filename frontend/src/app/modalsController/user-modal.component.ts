import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Input, OnChanges, Output, SimpleChanges } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Company, Establishment, User, UserRole } from '../models';
import { ModalShellComponent } from './modal-shell.component';

export interface UserModalPayload {
  id?: string;
  companyId?: string;
  name: string;
  email: string;
  password?: string;
  role: UserRole;
  assignedEstablishments: string[];
}

@Component({
  selector: 'app-user-modal',
  standalone: true,
  imports: [CommonModule, FormsModule, ModalShellComponent],
  template: `
    <app-modal-shell width="760px" labelledBy="user-modal-title" (closed)="closed.emit()">
      <div class="panel-head modal-head">
        <div>
          <h2 id="user-modal-title">{{ editingId ? 'Editar usuario' : 'Nuevo usuario' }}</h2>
          <p class="muted" *ngIf="!editingId">La contrasena inicial se genera automaticamente y se envia al correo del usuario.</p>
          <p class="muted" *ngIf="editingId">Actualiza los datos sin salir de la lista. La contrasena es opcional en edicion.</p>
        </div>
        <button class="icon-btn" type="button" (click)="closed.emit()" aria-label="Cerrar">×</button>
      </div>

      <div class="form-grid">
        <label><span>Nombre</span><input [(ngModel)]="name"></label>
        <label><span>Email</span><input [(ngModel)]="email" type="email"></label>
        <label *ngIf="currentUserRole === 'superusuario'">
          <span>Empresa</span>
          <select [(ngModel)]="companyId" (ngModelChange)="filterEstablishmentsByCompany()">
            <option value="">Seleccione empresa</option>
            <option *ngFor="let item of companies" [value]="item.id">{{ item.name }}</option>
          </select>
        </label>
        <label>
          <span>Rol</span>
          <select [(ngModel)]="role">
            <option value="administrador">Administrador</option>
            <option value="editor">Editor</option>
            <option value="visualizador">Visualizador</option>
          </select>
        </label>
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

      <p *ngIf="errorMessage" class="feedback error">{{ errorMessage }}</p>

      <div class="actions">
        <button class="btn" type="button" (click)="save()">{{ editingId ? 'Actualizar' : 'Crear y enviar acceso' }}</button>
        <button class="btn ghost" type="button" (click)="closed.emit()">Cancelar</button>
      </div>
    </app-modal-shell>
  `,
  styles: [`
    .panel-head { display:grid; gap:6px; }
    .modal-head { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; }
    .form-grid { display:grid; gap:14px; grid-template-columns: repeat(2, minmax(0,1fr)); }
    .full { grid-column:1 / -1; }
    label { display:grid; gap:8px; color:#22314d; font-weight:700; }
    input, select { width:100%; border:1px solid rgba(71, 85, 105, .18); border-radius:18px; padding:14px 16px; background:linear-gradient(180deg, #ffffff, #f8fafc); }
    .chips, .actions { display:flex; gap:10px; flex-wrap:wrap; }
    .chip, .btn { border:0; border-radius:999px; padding:10px 16px; cursor:pointer; font:inherit; }
    .chip { background:linear-gradient(135deg, rgba(15,23,42,.05), rgba(106,166,217,.16)); color:#24466b; border:1px solid rgba(71, 85, 105, .12); }
    .chip.active, .btn { background:linear-gradient(135deg, #0f172a, #2f6ea5); color:#fff; box-shadow:0 14px 28px rgba(15,23,42,.18); }
    .btn.ghost { background:linear-gradient(135deg, rgba(15,23,42,.05), rgba(106,166,217,.16)); color:#1e3a5f; border:1px solid rgba(71, 85, 105, .12); box-shadow:none; }
    .feedback { margin:0; font-weight:700; }
    .feedback.error { color:var(--danger); }
    .icon-btn { width:42px; height:42px; border:0; border-radius:999px; background:rgba(15,23,42,.08); color:#24466b; font-size:24px; cursor:pointer; }
    .muted { color:var(--muted); }
    @media (max-width: 768px) { .form-grid { grid-template-columns:1fr; } }
  `],
})
export class UserModalComponent implements OnChanges {
  @Input() user: User | null = null;
  @Input() companies: Company[] = [];
  @Input() establishments: Establishment[] = [];
  @Input() currentUserRole: UserRole | undefined;
  @Input() currentCompanyId = '';
  @Input() errorMessage = '';
  @Output() saved = new EventEmitter<UserModalPayload>();
  @Output() closed = new EventEmitter<void>();

  filteredEstablishments: Establishment[] = [];
  editingId = '';
  companyId = '';
  name = '';
  email = '';
  password = '';
  role: UserRole = 'visualizador';
  assigned: string[] = [];

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['user'] || changes['establishments']) {
      this.loadUser();
      this.filterEstablishmentsByCompany();
    }
  }

  filterEstablishmentsByCompany(): void {
    const activeCompanyId = this.currentUserRole === 'superusuario' ? this.companyId : this.currentCompanyId;
    this.filteredEstablishments = activeCompanyId
      ? this.establishments.filter((item) => item.companyId === activeCompanyId)
      : this.establishments;
    this.assigned = this.assigned.filter((item) => this.filteredEstablishments.some((establishment) => establishment.id === item));
  }

  toggleEstablishment(id: string): void {
    this.assigned = this.assigned.includes(id) ? this.assigned.filter((item) => item !== id) : [...this.assigned, id];
  }

  save(): void {
    this.saved.emit({
      id: this.editingId || undefined,
      companyId: this.currentUserRole === 'superusuario' ? this.companyId : this.currentCompanyId || undefined,
      name: this.name,
      email: this.email,
      password: this.password || undefined,
      role: this.role,
      assignedEstablishments: this.assigned,
    });
  }

  private loadUser(): void {
    if (!this.user) {
      this.editingId = '';
      this.companyId = '';
      this.name = '';
      this.email = '';
      this.password = '';
      this.role = 'visualizador';
      this.assigned = [];
      return;
    }

    this.editingId = this.user.id;
    this.companyId = this.user.companyId ?? '';
    this.name = this.user.name;
    this.email = this.user.email;
    this.password = '';
    this.role = this.user.role;
    this.assigned = [...this.user.assignedEstablishments];
  }
}
