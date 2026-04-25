import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Input, OnChanges, Output, SimpleChanges } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { ModalShellComponent } from './modal-shell.component';

export interface AdminUserDraft {
  name: string;
  email: string;
  password: string;
}

@Component({
  selector: 'app-admin-user-modal',
  standalone: true,
  imports: [CommonModule, FormsModule, ModalShellComponent],
  template: `
    <app-modal-shell width="560px" [elevated]="true" labelledBy="admin-modal-title" (closed)="closed.emit()">
      <div class="modal-head">
        <div>
          <h2 id="admin-modal-title">Nuevo usuario administrador</h2>
          <p class="muted">Este usuario se creara junto con la empresa y quedara asociado de inmediato.</p>
        </div>
        <button class="icon-btn" type="button" (click)="closed.emit()" aria-label="Cerrar">×</button>
      </div>

      <div class="form-grid">
        <label><span>Nombre</span><input [(ngModel)]="name"></label>
        <label><span>Email</span><input [(ngModel)]="email" type="email"></label>
        <label class="full"><span>Contrasena</span><input [(ngModel)]="password" type="password"></label>
      </div>

      <div class="actions">
        <button class="btn" type="button" (click)="apply()">Guardar usuario</button>
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
    .icon-btn { width:38px; height:38px; border:0; border-radius:999px; background:rgba(15,23,42,.08); color:#1e3a5f; cursor:pointer; font-size:24px; line-height:1; }
    .muted { color:var(--muted); }
    @media (max-width: 768px) { .form-grid { grid-template-columns:1fr; } }
  `],
})
export class AdminUserModalComponent implements OnChanges {
  @Input() draft: AdminUserDraft = { name: '', email: '', password: '' };
  @Output() applied = new EventEmitter<AdminUserDraft>();
  @Output() closed = new EventEmitter<void>();

  name = '';
  email = '';
  password = '';

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['draft']) {
      this.name = this.draft.name;
      this.email = this.draft.email;
      this.password = this.draft.password;
    }
  }

  apply(): void {
    if (!this.name.trim() || !this.email.trim() || !this.password.trim()) {
      return;
    }

    this.applied.emit({
      name: this.name.trim(),
      email: this.email.trim().toLowerCase(),
      password: this.password,
    });
  }
}
