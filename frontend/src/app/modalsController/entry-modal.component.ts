import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Input, OnChanges, Output, SimpleChanges } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Category, CategoryScope, Establishment, Transaction } from '../models';
import { ModalShellComponent } from './modal-shell.component';

export type EntryMode = 'transaction' | 'movement';
export type TransactionKind = 'income' | 'expense';

export interface EntryCategoryRequest {
  type: 'income' | 'expense' | 'movement';
  establishmentId: string;
}

export interface EntryModalPayload {
  mode: EntryMode;
  editingTransactionId: string;
  editingMovementGroupId: string;
  transactionType: TransactionKind;
  sourceEstablishmentId: string;
  destinationEstablishmentId: string;
  amount: number;
  categoryId: string;
  categoryName: string;
  categoryScope: CategoryScope;
  description: string;
  date: string;
}

@Component({
  selector: 'app-entry-modal',
  standalone: true,
  imports: [CommonModule, FormsModule, ModalShellComponent],
  template: `
    <app-modal-shell width="860px" labelledBy="entry-modal-title" (closed)="closed.emit()">
      <div class="panel-head modal-head">
        <div>
          <h2 id="entry-modal-title">{{ editingTransactionId ? (mode === 'movement' ? 'Editar movimiento' : 'Editar transaccion') : (mode === 'movement' ? 'Nuevo movimiento interno' : 'Nueva transaccion') }}</h2>
          <p class="muted">
            {{ mode === 'movement'
              ? 'El movimiento crea una salida en origen y un ingreso en destino sin afectar el total general de la empresa.'
              : 'Registra ingresos o gastos reales del establecimiento y actualiza su saldo.' }}
          </p>
        </div>
        <button class="icon-btn" type="button" (click)="closed.emit()" aria-label="Cerrar">×</button>
      </div>

      <div class="form-grid">
        <label *ngIf="mode === 'transaction'">
          <span>Tipo</span>
          <select [(ngModel)]="transactionType" (ngModelChange)="onTransactionTypeChange()">
            <option value="income">Ingreso</option>
            <option value="expense">Gasto</option>
          </select>
        </label>

        <label *ngIf="mode === 'movement'">
          <span>Origen</span>
          <select [(ngModel)]="sourceEstablishmentId" (ngModelChange)="onSourceEstablishmentChange()">
            <option *ngFor="let item of companyEstablishments" [value]="item.id">{{ item.name }}</option>
          </select>
        </label>

        <label *ngIf="mode === 'movement'">
          <span>Destino</span>
          <select [(ngModel)]="destinationEstablishmentId">
            <option value="">Seleccione destino</option>
            <option *ngFor="let item of destinationOptions()" [value]="item.id">{{ item.name }}</option>
          </select>
        </label>

        <label>
          <span>Monto</span>
          <input [(ngModel)]="amount" type="number" min="1">
        </label>

        <label>
          <span>Categoria existente</span>
          <select [(ngModel)]="categoryId" (ngModelChange)="onCategorySelectionChange()">
            <option value="">Crear o usar nombre manual</option>
            <option *ngFor="let item of availableCategories" [value]="item.id">
              {{ item.name }} · {{ item.scope }}
            </option>
          </select>
        </label>

        <label>
          <span>{{ categoryId ? 'Nombre de categoria seleccionada' : 'Nombre de categoria' }}</span>
          <input [(ngModel)]="categoryName" (ngModelChange)="onCategoryNameChange()" placeholder="Ej. Caja menor, traslado interno, ventas">
        </label>

        <label>
          <span>Alcance de categoria nueva</span>
          <select [(ngModel)]="categoryScope" [disabled]="!!categoryId">
            <option value="ESTABLECIMIENTO">Del establecimiento</option>
            <option value="EMPRESA">General de empresa</option>
          </select>
        </label>

        <label>
          <span>Fecha</span>
          <input [(ngModel)]="date" type="date">
        </label>

        <label class="full">
          <span>Descripcion</span>
          <textarea [(ngModel)]="description" rows="3"></textarea>
        </label>
      </div>

      <p *ngIf="errorMessage" class="feedback error">{{ errorMessage }}</p>

      <div class="actions">
        <button class="btn" type="button" (click)="save()">{{ editingTransactionId ? 'Actualizar' : 'Guardar' }}</button>
        <button class="btn ghost" type="button" (click)="closed.emit()">Cancelar</button>
      </div>
    </app-modal-shell>
  `,
  styles: [`
    .panel-head { display:grid; gap:6px; }
    .modal-head { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; }
    .modal-head h2, .modal-head p { margin:0; }
    .form-grid { display:grid; gap:14px; grid-template-columns: repeat(2, minmax(0,1fr)); }
    .full { grid-column:1 / -1; }
    label { display:grid; gap:8px; }
    input, textarea, select { width:100%; border:1px solid rgba(71, 85, 105, .18); border-radius:18px; padding:14px 16px; background:linear-gradient(180deg, #ffffff, #f8fafc); font:inherit; }
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
export class EntryModalComponent implements OnChanges {
  @Input() mode: EntryMode = 'transaction';
  @Input() establishmentId = '';
  @Input() transaction: Transaction | null = null;
  @Input() availableCategories: Category[] = [];
  @Input() companyEstablishments: Establishment[] = [];
  @Input() errorMessage = '';
  @Output() categoryLoadRequested = new EventEmitter<EntryCategoryRequest>();
  @Output() saved = new EventEmitter<EntryModalPayload>();
  @Output() closed = new EventEmitter<void>();

  editingTransactionId = '';
  editingMovementGroupId = '';
  transactionType: TransactionKind = 'expense';
  sourceEstablishmentId = '';
  destinationEstablishmentId = '';
  amount = 0;
  categoryId = '';
  categoryName = '';
  categoryScope: CategoryScope = 'ESTABLECIMIENTO';
  description = '';
  date = new Date().toISOString().slice(0, 10);

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['mode'] || changes['transaction'] || changes['establishmentId']) {
      this.loadForm();
      this.requestCategories();
    }

    if (changes['availableCategories'] && this.categoryId) {
      this.applySelectedCategory();
    }
  }

  onTransactionTypeChange(): void {
    this.categoryId = '';
    this.categoryName = '';
    this.categoryScope = 'ESTABLECIMIENTO';
    this.requestCategories();
  }

  onSourceEstablishmentChange(): void {
    if (this.mode === 'movement' && this.destinationEstablishmentId === this.sourceEstablishmentId) {
      this.destinationEstablishmentId = '';
    }
    this.categoryId = '';
    this.categoryName = '';
    this.requestCategories();
  }

  onCategorySelectionChange(): void {
    this.applySelectedCategory();
  }

  onCategoryNameChange(): void {
    const selected = this.availableCategories.find((item) => item.id === this.categoryId);
    if (selected && selected.name !== this.categoryName) {
      this.categoryId = '';
    }
  }

  destinationOptions(): Establishment[] {
    return this.companyEstablishments.filter((item) => item.id !== this.sourceEstablishmentId);
  }

  save(): void {
    this.saved.emit({
      mode: this.mode,
      editingTransactionId: this.editingTransactionId,
      editingMovementGroupId: this.editingMovementGroupId,
      transactionType: this.transactionType,
      sourceEstablishmentId: this.sourceEstablishmentId,
      destinationEstablishmentId: this.destinationEstablishmentId,
      amount: this.amount,
      categoryId: this.categoryId,
      categoryName: this.categoryName,
      categoryScope: this.categoryScope,
      description: this.description,
      date: this.date,
    });
  }

  private loadForm(): void {
    this.editingTransactionId = '';
    this.editingMovementGroupId = '';
    this.transactionType = 'expense';
    this.sourceEstablishmentId = this.establishmentId;
    this.destinationEstablishmentId = '';
    this.amount = 0;
    this.categoryId = '';
    this.categoryName = '';
    this.categoryScope = this.mode === 'movement' ? 'EMPRESA' : 'ESTABLECIMIENTO';
    this.description = '';
    this.date = new Date().toISOString().slice(0, 10);

    if (!this.transaction) {
      return;
    }

    this.editingTransactionId = this.transaction.id;
    this.amount = this.transaction.amount;
    this.description = this.transaction.description;
    this.date = this.transaction.date.slice(0, 10);
    this.categoryId = this.transaction.categoryId ?? '';
    this.categoryName = this.transaction.category;

    if (this.isMovement(this.transaction)) {
      this.editingMovementGroupId = this.transaction.movementGroupId ?? '';
      if (this.transaction.type === 'SALIDA_POR_MOVIMIENTO') {
        this.sourceEstablishmentId = this.transaction.establishmentId;
        this.destinationEstablishmentId = this.transaction.relatedEstablishmentId ?? '';
      } else {
        this.sourceEstablishmentId = this.transaction.relatedEstablishmentId ?? '';
        this.destinationEstablishmentId = this.transaction.establishmentId;
      }
      this.categoryScope = 'EMPRESA';
    } else {
      this.transactionType = this.transaction.type as TransactionKind;
      this.sourceEstablishmentId = this.transaction.establishmentId;
      this.categoryScope = 'ESTABLECIMIENTO';
    }
  }

  private requestCategories(): void {
    this.categoryLoadRequested.emit({
      type: this.mode === 'movement' ? 'movement' : this.transactionType,
      establishmentId: this.sourceEstablishmentId || this.establishmentId,
    });
  }

  private applySelectedCategory(): void {
    if (!this.categoryId) {
      return;
    }

    const selected = this.availableCategories.find((item) => item.id === this.categoryId);
    if (!selected) {
      return;
    }

    this.categoryName = selected.name;
    this.categoryScope = selected.scope;
  }

  private isMovement(item: Transaction): boolean {
    return item.type === 'SALIDA_POR_MOVIMIENTO' || item.type === 'INGRESO_POR_MOVIMIENTO';
  }
}
