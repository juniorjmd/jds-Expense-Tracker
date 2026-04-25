import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Input, Output } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { ModalShellComponent } from './modal-shell.component';

export interface ExpenseTemplateModalPayload {
  category: string;
  description: string;
  amount: number;
}

@Component({
  selector: 'app-expense-template-modal',
  standalone: true,
  imports: [CommonModule, FormsModule, ModalShellComponent],
  template: `
    <app-modal-shell width="760px" labelledBy="template-modal-title" (closed)="closed.emit()">
      <div class="panel-head modal-head">
        <div>
          <h2 id="template-modal-title">Nuevo gasto predeterminado</h2>
          <p class="muted">Guarda un gasto recurrente sin perder de vista el historial del establecimiento.</p>
        </div>
        <button class="icon-btn" type="button" (click)="closed.emit()" aria-label="Cerrar">×</button>
      </div>

      <div class="form-grid">
        <label><span>Categoria</span><input [(ngModel)]="category"></label>
        <label><span>Monto</span><input [(ngModel)]="amount" type="number" min="1"></label>
        <label class="full"><span>Descripcion</span><textarea [(ngModel)]="description" rows="3"></textarea></label>
      </div>

      <p *ngIf="errorMessage" class="feedback error">{{ errorMessage }}</p>

      <div class="actions">
        <button class="btn" type="button" (click)="saved.emit({ category, description, amount })">Guardar</button>
        <button class="btn ghost" type="button" (click)="closed.emit()">Cancelar</button>
      </div>
    </app-modal-shell>
  `,
  styles: [`
    .panel-head { display:grid; gap:6px; }
    .modal-head { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; }
    .form-grid { display:grid; gap:14px; grid-template-columns: repeat(2, minmax(0,1fr)); }
    .full { grid-column:1 / -1; }
    label { display:grid; gap:8px; }
    input, textarea { width:100%; border:1px solid rgba(71, 85, 105, .18); border-radius:18px; padding:14px 16px; background:linear-gradient(180deg, #ffffff, #f8fafc); font:inherit; }
    .actions { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
    .btn { border:0; border-radius:999px; padding:12px 18px; background:linear-gradient(135deg, #0f172a, #2f6ea5); color:#fff; cursor:pointer; font-weight:700; box-shadow:0 16px 30px rgba(15, 23, 42, .20); }
    .btn.ghost { background:linear-gradient(135deg, rgba(47,110,165,.12), rgba(106,166,217,.22)); color:#24466b; border:1px solid rgba(71, 85, 105, .10); box-shadow:none; }
    .feedback { margin:0; font-weight:700; }
    .feedback.error { color:var(--danger); }
    .icon-btn { width:42px; height:42px; border:0; border-radius:999px; background:rgba(15,23,42,.08); color:#24466b; font-size:24px; cursor:pointer; }
    .muted { color:var(--muted); }
    @media (max-width: 920px) { .form-grid { grid-template-columns:1fr; } }
  `],
})
export class ExpenseTemplateModalComponent {
  @Input() errorMessage = '';
  @Output() saved = new EventEmitter<ExpenseTemplateModalPayload>();
  @Output() closed = new EventEmitter<void>();

  category = '';
  description = '';
  amount = 0;
}
