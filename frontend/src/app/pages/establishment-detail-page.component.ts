import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { Category, CategoryScope, Establishment, ExpenseTemplate, Transaction } from '../models';
import { ApiRequestError } from '../services/api.service';
import { AuthService } from '../services/auth.service';
import { StorageService } from '../services/storage.service';
import { SummaryService } from '../services/summary.service';
import { ModalShellComponent } from '../modalsComponent/modal-shell.component';

type EntryMode = 'transaction' | 'movement';
type TransactionKind = 'income' | 'expense';

@Component({
  selector: 'app-establishment-detail-page',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink, ModalShellComponent],
  template: `
    <div class="shell" *ngIf="establishment as current">
      <header class="hero">
        <div class="hero-copy">
          <a routerLink="/" class="back">Volver</a>
          <p class="eyebrow">Operacion por establecimiento</p>
          <h1>{{ current.name }}</h1>
          <p class="muted">{{ current.description || 'Sin descripcion' }}</p>
        </div>

        <div class="hero-note">
          <strong>{{ current.companyName || 'Empresa activa' }}</strong>
          <span>
            {{ current.transactionCount ?? 0 }} movimientos del mes
            · Balance {{ currency(summary.balance) }}
          </span>
          <div class="actions" *ngIf="auth.can('edit')">
            <button class="btn" type="button" (click)="openTransactionModal()">Nueva transaccion</button>
            <button class="btn ghost" type="button" (click)="openMovementModal()">Nuevo movimiento</button>
            <button class="btn ghost" type="button" (click)="toggleTemplateModal()">
              {{ showTemplateForm ? 'Cancelar predeterminado' : 'Gasto predeterminado' }}
            </button>
          </div>
        </div>
      </header>

      <section class="grid metrics">
        <article class="metric">
          <span>Ingresos</span>
          <strong class="income">{{ currency(summary.income) }}</strong>
          <small>Incluye ingresos reales y entradas por movimiento.</small>
        </article>
        <article class="metric">
          <span>Gastos</span>
          <strong class="expense">{{ currency(summary.expense) }}</strong>
          <small>Incluye gastos reales y salidas por movimiento.</small>
        </article>
        <article class="metric">
          <span>Balance</span>
          <strong [class.income]="summary.balance >= 0" [class.expense]="summary.balance < 0">{{ currency(summary.balance) }}</strong>
          <small>Resultado acumulado del periodo activo.</small>
        </article>
      </section>

      <section class="panel" *ngIf="successMessage || errorMessage">
        <p *ngIf="successMessage" class="feedback success">{{ successMessage }}</p>
        <p *ngIf="errorMessage" class="feedback error">{{ errorMessage }}</p>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Gastos predeterminados</h2><p class="muted">Aplicalos rapido al flujo del establecimiento.</p></div>
        <div class="list-grid" *ngIf="templates.length; else noTemplates">
          <article class="row-card" *ngFor="let item of templates">
            <div><strong>{{ item.category }}</strong><p class="muted">{{ item.description }}</p></div>
            <div class="actions">
              <span class="amount">{{ currency(item.amount) }}</span>
              <button class="btn ghost" type="button" (click)="applyTemplate(item)">Aplicar</button>
              <button class="btn ghost" type="button" (click)="deleteTemplate(item.id)">Eliminar</button>
            </div>
          </article>
        </div>
        <ng-template #noTemplates><p class="muted">No hay gastos predeterminados.</p></ng-template>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Transacciones</h2><p class="muted">Historial del establecimiento seleccionado.</p></div>
        <div class="list-grid" *ngIf="transactions.length; else noTransactions">
          <article class="row-card" *ngFor="let item of transactions">
            <div class="entry-copy">
              <div class="entry-head">
                <strong>{{ item.category }}</strong>
                <span class="type-pill" [class.movement]="isMovement(item)" [class.income-tone]="isIncoming(item)" [class.expense-tone]="isOutgoing(item)">
                  {{ typeLabel(item) }}
                </span>
              </div>
              <p class="muted">{{ item.description || movementDescription(item) }}</p>
              <small>{{ item.date | date:'mediumDate' }}</small>
            </div>
            <div class="actions entry-actions">
              <span class="amount" [class.income]="isIncoming(item)" [class.expense]="isOutgoing(item)">{{ currency(item.amount) }}</span>
              <button *ngIf="auth.can('edit')" class="btn ghost" type="button" (click)="openEditModal(item)">Editar</button>
              <button *ngIf="auth.can('edit')" class="btn ghost" type="button" (click)="deleteTransaction(item.id)">Eliminar</button>
            </div>
          </article>
        </div>
        <ng-template #noTransactions><p class="muted">No hay movimientos registrados.</p></ng-template>
      </section>

      <app-modal-shell *ngIf="showEntryModal" width="860px" labelledBy="entry-modal-title" (closed)="closeEntryModal()">
          <div class="panel-head modal-head">
            <div>
              <h2 id="entry-modal-title">{{ editingTransactionId ? (entryMode === 'movement' ? 'Editar movimiento' : 'Editar transaccion') : (entryMode === 'movement' ? 'Nuevo movimiento interno' : 'Nueva transaccion') }}</h2>
              <p class="muted">
                {{ entryMode === 'movement'
                  ? 'El movimiento crea una salida en origen y un ingreso en destino sin afectar el total general de la empresa.'
                  : 'Registra ingresos o gastos reales del establecimiento y actualiza su saldo.' }}
              </p>
            </div>
            <button class="icon-btn" type="button" (click)="closeEntryModal()" aria-label="Cerrar">×</button>
          </div>

          <div class="form-grid">
            <label *ngIf="entryMode === 'transaction'">
              <span>Tipo</span>
              <select [(ngModel)]="entryTransactionType" (ngModelChange)="onTransactionTypeChange()">
                <option value="income">Ingreso</option>
                <option value="expense">Gasto</option>
              </select>
            </label>

            <label *ngIf="entryMode === 'movement'">
              <span>Origen</span>
              <select [(ngModel)]="entrySourceEstablishmentId" (ngModelChange)="onSourceEstablishmentChange()">
                <option *ngFor="let item of companyEstablishments" [value]="item.id">{{ item.name }}</option>
              </select>
            </label>

            <label *ngIf="entryMode === 'movement'">
              <span>Destino</span>
              <select [(ngModel)]="entryDestinationEstablishmentId">
                <option value="">Seleccione destino</option>
                <option *ngFor="let item of destinationOptions()" [value]="item.id">{{ item.name }}</option>
              </select>
            </label>

            <label>
              <span>Monto</span>
              <input [(ngModel)]="entryAmount" type="number" min="1">
            </label>

            <label>
              <span>Categoria existente</span>
              <select [(ngModel)]="entryCategoryId" (ngModelChange)="onCategorySelectionChange()">
                <option value="">Crear o usar nombre manual</option>
                <option *ngFor="let item of availableCategories" [value]="item.id">
                  {{ item.name }} · {{ item.scope }}
                </option>
              </select>
            </label>

            <label>
              <span>{{ entryCategoryId ? 'Nombre de categoria seleccionada' : 'Nombre de categoria' }}</span>
              <input [(ngModel)]="entryCategoryName" (ngModelChange)="onCategoryNameChange()" placeholder="Ej. Caja menor, traslado interno, ventas">
            </label>

            <label>
              <span>Alcance de categoria nueva</span>
              <select [(ngModel)]="entryCategoryScope" [disabled]="!!entryCategoryId">
                <option value="ESTABLECIMIENTO">Del establecimiento</option>
                <option value="EMPRESA">General de empresa</option>
              </select>
            </label>

            <label>
              <span>Fecha</span>
              <input [(ngModel)]="entryDate" type="date">
            </label>

            <label class="full">
              <span>Descripcion</span>
              <textarea [(ngModel)]="entryDescription" rows="3"></textarea>
            </label>
          </div>

          <p *ngIf="entryErrorMessage" class="feedback error">{{ entryErrorMessage }}</p>

          <div class="actions">
            <button class="btn" type="button" (click)="saveEntry()">{{ editingTransactionId ? 'Actualizar' : 'Guardar' }}</button>
            <button class="btn ghost" type="button" (click)="closeEntryModal()">Cancelar</button>
          </div>
      </app-modal-shell>

      <app-modal-shell *ngIf="showTemplateForm" width="760px" labelledBy="template-modal-title" (closed)="closeTemplateModal()">
          <div class="panel-head modal-head">
            <div>
              <h2 id="template-modal-title">Nuevo gasto predeterminado</h2>
              <p class="muted">Guarda un gasto recurrente sin perder de vista el historial del establecimiento.</p>
            </div>
            <button class="icon-btn" type="button" (click)="closeTemplateModal()" aria-label="Cerrar">×</button>
          </div>

          <div class="form-grid">
            <label><span>Categoria</span><input [(ngModel)]="templateCategory"></label>
            <label><span>Monto</span><input [(ngModel)]="templateAmount" type="number" min="1"></label>
            <label class="full"><span>Descripcion</span><textarea [(ngModel)]="templateDescription" rows="3"></textarea></label>
          </div>

          <p *ngIf="templateErrorMessage" class="feedback error">{{ templateErrorMessage }}</p>

          <div class="actions">
            <button class="btn" type="button" (click)="addTemplate()">Guardar</button>
            <button class="btn ghost" type="button" (click)="closeTemplateModal()">Cancelar</button>
          </div>
      </app-modal-shell>
    </div>
  `,
  styles: [`
    .shell { padding: 32px; display: grid; gap: 24px; }
    .hero, .panel, .metric, .row-card { background: var(--surface); border: 1px solid var(--surface-border); border-radius: 28px; box-shadow: var(--shadow-card); backdrop-filter: blur(14px); }
    .hero { padding: 30px 32px; display: flex; justify-content: space-between; gap: 20px; background: linear-gradient(135deg, rgba(15,23,42,.97), rgba(30,58,95,.94) 48%, rgba(47,110,165,.90)); color: #fff; position: relative; overflow: hidden; }
    .hero::after { content: ""; position: absolute; inset: auto -6% -38% auto; width: 240px; height: 240px; border-radius: 999px; background: radial-gradient(circle, rgba(244,162,97,.30), transparent 68%); pointer-events: none; }
    .hero-copy { display: grid; gap: 8px; max-width: 640px; }
    .hero-note { max-width: 420px; display: grid; gap: 8px; padding: 18px; border-radius: 22px; background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.16); align-content: start; }
    .eyebrow { margin: 8px 0 0; text-transform: uppercase; letter-spacing: .2em; font-size: 11px; font-weight: 800; color: rgba(255,255,255,.7); }
    .metrics { display:grid; gap:16px; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
    .metric { padding: 22px; display:grid; gap:8px; background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(241,245,249,.90)); }
    .metric strong { font-size: 28px; letter-spacing: -.04em; }
    .panel { padding: 24px; display: grid; gap: 18px; background: var(--surface-strong); }
    .panel-head { display: grid; gap: 6px; }
    .actions { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
    .btn { border:0; border-radius:999px; padding:12px 18px; background:linear-gradient(135deg, #0f172a, #2f6ea5); color:#fff; cursor:pointer; font-weight:700; text-decoration:none; box-shadow:0 16px 30px rgba(15, 23, 42, .20); }
    .ghost { background:linear-gradient(135deg, rgba(255,255,255,.14), rgba(255,255,255,.08)); color:#fff; border:1px solid rgba(255,255,255,.18); box-shadow:none; }
    .panel .ghost, .row-card .ghost { background:linear-gradient(135deg, rgba(47,110,165,.12), rgba(106,166,217,.22)); color:#24466b; border:1px solid rgba(71, 85, 105, .10); }
    .back { text-decoration:none; color:inherit; font-weight:700; }
    .muted, small { color:var(--muted); }
    .hero .muted, .hero-note span { color:rgba(255,255,255,.78); }
    h1, h2, p { margin: 0; }
    .form-grid { display:grid; gap:14px; grid-template-columns: repeat(2, minmax(0,1fr)); }
    .full { grid-column:1 / -1; }
    label { display:grid; gap:8px; }
    input, textarea, select { width:100%; border:1px solid rgba(71, 85, 105, .18); border-radius:18px; padding:14px 16px; background:linear-gradient(180deg, #ffffff, #f8fafc); font:inherit; }
    .list-grid { display:grid; gap:12px; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); }
    .row-card { padding: 20px; display:flex; justify-content:space-between; gap:16px; border-radius:22px; background:linear-gradient(180deg, rgba(255,255,255,.98), rgba(241,245,249,.90)); align-items:flex-start; }
    .entry-copy { display:grid; gap:8px; }
    .entry-head { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
    .entry-actions { justify-content:flex-end; }
    .type-pill { display:inline-flex; align-items:center; border-radius:999px; padding:6px 10px; font-size:12px; font-weight:700; background:rgba(15,23,42,.07); color:#334155; }
    .type-pill.movement { background:linear-gradient(135deg, rgba(244,162,97,.18), rgba(255,255,255,.6)); color:#8a5426; }
    .type-pill.income-tone { background:linear-gradient(135deg, rgba(34,197,94,.14), rgba(255,255,255,.55)); color:#166534; }
    .type-pill.expense-tone { background:linear-gradient(135deg, rgba(239,68,68,.12), rgba(255,255,255,.55)); color:#991b1b; }
    .amount { font-weight:700; }
    .income { color:var(--success); }
    .expense { color:var(--danger); }
    .feedback { margin:0; font-weight:700; }
    .feedback.success { color:var(--success); }
    .feedback.error { color:var(--danger); }
    .modal-head { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; }
    .icon-btn { width:42px; height:42px; border:0; border-radius:999px; background:rgba(15,23,42,.08); color:#24466b; font-size:24px; cursor:pointer; }
    @media (max-width: 920px) { .shell { padding: 18px; } .hero, .row-card { flex-direction:column; } .form-grid { grid-template-columns:1fr; } .list-grid { grid-template-columns:1fr; } .modal-backdrop { padding:16px; } }
  `],
})
export class EstablishmentDetailPageComponent implements OnInit {
  establishment: Establishment | undefined;
  transactions: Transaction[] = [];
  templates: ExpenseTemplate[] = [];
  availableCategories: Category[] = [];
  companyEstablishments: Establishment[] = [];
  summary = { month: new Date().toISOString().slice(0, 7), income: 0, expense: 0, balance: 0 };
  establishmentId = '';
  showTemplateForm = false;
  showEntryModal = false;
  entryMode: EntryMode = 'transaction';
  editingTransactionId = '';
  editingMovementGroupId = '';
  entryTransactionType: TransactionKind = 'expense';
  entrySourceEstablishmentId = '';
  entryDestinationEstablishmentId = '';
  entryAmount = 0;
  entryCategoryId = '';
  entryCategoryName = '';
  entryCategoryScope: CategoryScope = 'ESTABLECIMIENTO';
  entryDescription = '';
  entryDate = new Date().toISOString().slice(0, 10);
  entryErrorMessage = '';
  templateCategory = '';
  templateDescription = '';
  templateAmount = 0;
  templateErrorMessage = '';
  successMessage = '';
  errorMessage = '';

  constructor(
    public auth: AuthService,
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly storage: StorageService,
    private readonly summaryService: SummaryService
  ) {}

  async ngOnInit(): Promise<void> {
    const id = this.route.snapshot.paramMap.get('id') ?? '';
    if (!this.auth.canAccessEstablishment(id)) {
      await this.router.navigate(['/']);
      return;
    }

    this.establishmentId = id;
    await this.refresh();
  }

  async refresh(): Promise<void> {
    const month = new Date().toISOString().slice(0, 7);
    const establishments = await this.storage.getEstablishments(month);
    this.establishment = establishments.find((item) => item.id === this.establishmentId);
    if (!this.establishment) {
      await this.router.navigate(['/']);
      return;
    }

    this.companyEstablishments = establishments.filter((item) => item.companyId === this.establishment?.companyId);
    this.transactions = await this.storage.getTransactionsByEstablishment(this.establishmentId);
    this.templates = await this.storage.getExpenseTemplatesByEstablishment(this.establishmentId);
    this.summary = {
      month,
      income: this.establishment.income ?? 0,
      expense: this.establishment.expense ?? 0,
      balance: this.establishment.balance ?? 0,
    };
  }

  openTransactionModal(): void {
    this.resetEntryForm();
    this.entryMode = 'transaction';
    this.entrySourceEstablishmentId = this.establishmentId;
    void this.loadCategories();
    this.showEntryModal = true;
  }

  openMovementModal(): void {
    this.resetEntryForm();
    this.entryMode = 'movement';
    this.entrySourceEstablishmentId = this.establishmentId;
    this.entryCategoryScope = 'EMPRESA';
    void this.loadCategories();
    this.showEntryModal = true;
  }

  async openEditModal(item: Transaction): Promise<void> {
    this.resetEntryForm();
    this.editingTransactionId = item.id;
    this.entryAmount = item.amount;
    this.entryDescription = item.description;
    this.entryDate = item.date.slice(0, 10);
    this.entryCategoryId = item.categoryId ?? '';
    this.entryCategoryName = item.category;

    if (this.isMovement(item)) {
      this.entryMode = 'movement';
      this.editingMovementGroupId = item.movementGroupId ?? '';
      if (item.type === 'SALIDA_POR_MOVIMIENTO') {
        this.entrySourceEstablishmentId = item.establishmentId;
        this.entryDestinationEstablishmentId = item.relatedEstablishmentId ?? '';
      } else {
        this.entrySourceEstablishmentId = item.relatedEstablishmentId ?? '';
        this.entryDestinationEstablishmentId = item.establishmentId;
      }
      this.entryCategoryScope = 'EMPRESA';
    } else {
      this.entryMode = 'transaction';
      this.entryTransactionType = item.type as TransactionKind;
      this.entrySourceEstablishmentId = item.establishmentId;
      this.entryCategoryScope = 'ESTABLECIMIENTO';
    }

    await this.loadCategories();
    this.showEntryModal = true;
  }

  closeEntryModal(): void {
    this.showEntryModal = false;
    this.resetEntryForm();
  }

  toggleTemplateModal(): void {
    this.showTemplateForm = !this.showTemplateForm;
    this.templateErrorMessage = '';
  }

  closeTemplateModal(): void {
    this.showTemplateForm = false;
    this.templateCategory = '';
    this.templateDescription = '';
    this.templateAmount = 0;
    this.templateErrorMessage = '';
  }

  async saveEntry(): Promise<void> {
    if (!this.establishment) {
      return;
    }

    if (!this.entryCategoryName.trim()) {
      this.entryErrorMessage = 'VALIDATION_ERROR: La categoria es obligatoria.';
      return;
    }

    if (this.entryAmount <= 0) {
      this.entryErrorMessage = 'VALIDATION_ERROR: El monto debe ser mayor a cero.';
      return;
    }

    if (this.entryMode === 'movement' && (!this.entrySourceEstablishmentId || !this.entryDestinationEstablishmentId)) {
      this.entryErrorMessage = 'VALIDATION_ERROR: Debes seleccionar origen y destino.';
      return;
    }

    this.entryErrorMessage = '';
    this.errorMessage = '';
    this.successMessage = '';

    try {
      if (this.entryMode === 'movement') {
        const payload = {
          sourceEstablishmentId: this.entrySourceEstablishmentId,
          destinationEstablishmentId: this.entryDestinationEstablishmentId,
          amount: this.entryAmount,
          categoryId: this.entryCategoryId || null,
          category: this.entryCategoryName.trim(),
          categoryScope: this.entryCategoryScope,
          description: this.entryDescription.trim(),
          date: this.entryDate,
        };

        if (this.editingMovementGroupId) {
          await this.storage.updateMovement(this.editingMovementGroupId, payload);
          this.successMessage = 'Movimiento actualizado correctamente.';
        } else {
          await this.storage.saveMovement(payload);
          this.successMessage = 'Movimiento creado correctamente.';
        }
      } else {
        const payload = {
          establishmentId: this.entrySourceEstablishmentId || this.establishment.id,
          type: this.entryTransactionType,
          amount: this.entryAmount,
          categoryId: this.entryCategoryId || null,
          category: this.entryCategoryName.trim(),
          categoryScope: this.entryCategoryScope,
          description: this.entryDescription.trim(),
          date: this.entryDate,
        };

        if (this.editingTransactionId) {
          await this.storage.updateTransaction(this.editingTransactionId, payload);
          this.successMessage = 'Transaccion actualizada correctamente.';
        } else {
          await this.storage.saveTransaction(payload);
          this.successMessage = 'Transaccion creada correctamente.';
        }
      }

      await this.refresh();
      this.closeEntryModal();
    } catch (error) {
      this.entryErrorMessage = this.describeError(error);
    }
  }

  async addTemplate(): Promise<void> {
    if (!this.establishment || !this.templateCategory.trim() || this.templateAmount <= 0) {
      this.templateErrorMessage = 'VALIDATION_ERROR: Categoria y monto son obligatorios.';
      return;
    }

    this.templateErrorMessage = '';
    await this.storage.saveExpenseTemplate({
      establishmentId: this.establishment.id,
      category: this.templateCategory,
      description: this.templateDescription,
      amount: this.templateAmount,
    });

    this.closeTemplateModal();
    this.successMessage = 'Gasto predeterminado creado correctamente.';
    await this.refresh();
  }

  async applyTemplate(item: ExpenseTemplate): Promise<void> {
    await this.storage.applyExpenseTemplate(item);
    this.successMessage = 'Gasto predeterminado aplicado correctamente.';
    await this.refresh();
  }

  async deleteTemplate(id: string): Promise<void> {
    await this.storage.deleteExpenseTemplate(id);
    this.successMessage = 'Gasto predeterminado eliminado correctamente.';
    await this.refresh();
  }

  async deleteTransaction(id: string): Promise<void> {
    await this.storage.deleteTransaction(id);
    this.successMessage = 'Movimiento eliminado correctamente.';
    await this.refresh();
  }

  async loadCategories(): Promise<void> {
    const type = this.entryMode === 'movement' ? 'movement' : this.entryTransactionType;
    const establishmentId = this.entrySourceEstablishmentId || this.establishmentId;
    this.availableCategories = await this.storage.getCategories(type, establishmentId);

    if (this.entryCategoryId) {
      const selected = this.availableCategories.find((item) => item.id === this.entryCategoryId);
      if (selected) {
        this.entryCategoryName = selected.name;
        this.entryCategoryScope = selected.scope;
      }
    }
  }

  onTransactionTypeChange(): void {
    this.entryCategoryId = '';
    this.entryCategoryName = '';
    this.entryCategoryScope = 'ESTABLECIMIENTO';
    void this.loadCategories();
  }

  onSourceEstablishmentChange(): void {
    if (this.entryMode === 'movement' && this.entryDestinationEstablishmentId === this.entrySourceEstablishmentId) {
      this.entryDestinationEstablishmentId = '';
    }
    this.entryCategoryId = '';
    this.entryCategoryName = '';
    void this.loadCategories();
  }

  onCategorySelectionChange(): void {
    if (!this.entryCategoryId) {
      return;
    }

    const selected = this.availableCategories.find((item) => item.id === this.entryCategoryId);
    if (!selected) {
      return;
    }

    this.entryCategoryName = selected.name;
    this.entryCategoryScope = selected.scope;
  }

  onCategoryNameChange(): void {
    const selected = this.availableCategories.find((item) => item.id === this.entryCategoryId);
    if (selected && selected.name !== this.entryCategoryName) {
      this.entryCategoryId = '';
    }
  }

  destinationOptions(): Establishment[] {
    return this.companyEstablishments.filter((item) => item.id !== this.entrySourceEstablishmentId);
  }

  isMovement(item: Transaction): boolean {
    return item.type === 'SALIDA_POR_MOVIMIENTO' || item.type === 'INGRESO_POR_MOVIMIENTO';
  }

  isIncoming(item: Transaction): boolean {
    return item.type === 'income' || item.type === 'INGRESO_POR_MOVIMIENTO';
  }

  isOutgoing(item: Transaction): boolean {
    return item.type === 'expense' || item.type === 'SALIDA_POR_MOVIMIENTO';
  }

  typeLabel(item: Transaction): string {
    switch (item.type) {
      case 'income':
        return 'Ingreso';
      case 'expense':
        return 'Gasto';
      case 'SALIDA_POR_MOVIMIENTO':
        return 'Salida por movimiento';
      case 'INGRESO_POR_MOVIMIENTO':
        return 'Ingreso por movimiento';
      default:
        return item.type;
    }
  }

  movementDescription(item: Transaction): string {
    const related = this.companyEstablishments.find((candidate) => candidate.id === item.relatedEstablishmentId);
    if (!related) {
      return this.isMovement(item) ? 'Movimiento interno entre establecimientos.' : '';
    }

    return item.type === 'SALIDA_POR_MOVIMIENTO'
      ? `Movimiento interno hacia ${related.name}.`
      : `Movimiento interno recibido desde ${related.name}.`;
  }

  currency(value: number): string {
    return this.summaryService.formatCurrency(value);
  }

  private resetEntryForm(): void {
    this.entryMode = 'transaction';
    this.editingTransactionId = '';
    this.editingMovementGroupId = '';
    this.entryTransactionType = 'expense';
    this.entrySourceEstablishmentId = this.establishmentId;
    this.entryDestinationEstablishmentId = '';
    this.entryAmount = 0;
    this.entryCategoryId = '';
    this.entryCategoryName = '';
    this.entryCategoryScope = 'ESTABLECIMIENTO';
    this.entryDescription = '';
    this.entryDate = new Date().toISOString().slice(0, 10);
    this.entryErrorMessage = '';
    this.availableCategories = [];
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
