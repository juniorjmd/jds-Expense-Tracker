import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Input, Output } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Company } from '../models';
import { ModalShellComponent } from './modal-shell.component';

export interface EstablishmentModalPayload {
  name: string;
  description: string;
  companyId: string;
}

@Component({
  selector: 'app-establishment-modal',
  standalone: true,
  imports: [CommonModule, FormsModule, ModalShellComponent],
  template: `
    <app-modal-shell width="720px" labelledBy="establishment-modal-title" (closed)="closed.emit()">
      <div class="panel-head modal-head">
        <div>
          <h2 id="establishment-modal-title">Nuevo establecimiento</h2>
          <p class="muted">Crea el establecimiento sin perder el contexto del panel principal.</p>
        </div>
        <button class="icon-btn" type="button" (click)="closed.emit()" aria-label="Cerrar">×</button>
      </div>

      <div class="form-grid">
        <label>
          <span>Nombre</span>
          <input [(ngModel)]="name" placeholder="Ej. Sucursal Centro">
        </label>
        <label *ngIf="showCompanySelect">
          <span>Empresa</span>
          <select [(ngModel)]="companyId">
            <option value="">Seleccione una empresa</option>
            <option *ngFor="let item of companies" [value]="item.id">{{ item.name }}</option>
          </select>
        </label>
        <label class="full">
          <span>Descripcion</span>
          <textarea [(ngModel)]="description" rows="3" placeholder="Describe el establecimiento"></textarea>
        </label>
      </div>

      <div class="panel-actions">
        <button class="btn" type="button" (click)="saved.emit({ name, description, companyId })">Guardar</button>
        <button class="btn ghost" type="button" (click)="closed.emit()">Cancelar</button>
      </div>
    </app-modal-shell>
  `,
  styles: [`
    .panel-head { display:grid; gap:6px; }
    .modal-head { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; }
    .modal-head h2, .modal-head p { margin:0; }
    .form-grid { display:grid; gap:14px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .form-grid .full { grid-column: 1 / -1; }
    label { display:grid; gap:8px; color:#22314d; font-weight:700; }
    input, textarea, select { width:100%; border:1px solid rgba(71, 85, 105, .18); border-radius:18px; padding:14px 16px; background:linear-gradient(180deg, #ffffff, #f8fafc); box-shadow:inset 0 1px 0 rgba(255,255,255,.85); }
    .panel-actions { display:flex; gap:12px; flex-wrap:wrap; }
    .btn { border:0; border-radius:999px; padding:12px 18px; background:linear-gradient(135deg, #0f172a, #2f6ea5); color:#fff; text-decoration:none; cursor:pointer; font-weight:700; box-shadow:0 16px 30px rgba(15, 23, 42, .20); }
    .btn.ghost { background:linear-gradient(135deg, rgba(15,23,42,.05), rgba(106,166,217,.16)); color:#1e3a5f; border:1px solid rgba(71, 85, 105, .14); box-shadow:none; }
    .icon-btn { width:42px; height:42px; border:0; border-radius:999px; background:rgba(15,23,42,.08); color:#24466b; font-size:24px; cursor:pointer; }
    .muted { color:var(--muted); }
    @media (max-width: 768px) { .form-grid { grid-template-columns:1fr; } }
  `],
})
export class EstablishmentModalComponent {
  @Input() companies: Company[] = [];
  @Input() showCompanySelect = false;
  @Output() saved = new EventEmitter<EstablishmentModalPayload>();
  @Output() closed = new EventEmitter<void>();

  name = '';
  description = '';
  companyId = '';
}
