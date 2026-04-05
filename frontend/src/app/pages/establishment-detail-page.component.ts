import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { Establishment, ExpenseTemplate, Transaction } from '../models';
import { AuthService } from '../services/auth.service';
import { StorageService } from '../services/storage.service';
import { SummaryService } from '../services/summary.service';

@Component({
  selector: 'app-establishment-detail-page',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  template: `
    <div class="shell" *ngIf="establishment as current">
      <header class="topbar">
        <div>
          <a routerLink="/" class="back">Volver</a>
          <h1>{{ current.name }}</h1>
          <p class="muted">{{ current.description || 'Sin descripcion' }}</p>
        </div>

        <div class="actions">
          <button *ngIf="auth.can('edit')" class="btn" type="button" (click)="showTransactionForm = !showTransactionForm">
            {{ showTransactionForm ? 'Cancelar ingreso/gasto' : 'Nueva transaccion' }}
          </button>
          <button *ngIf="auth.can('edit')" class="btn ghost" type="button" (click)="showTemplateForm = !showTemplateForm">
            {{ showTemplateForm ? 'Cancelar predeterminado' : 'Gasto predeterminado' }}
          </button>
        </div>
      </header>

      <section class="grid metrics">
        <article class="metric"><span>Ingresos</span><strong class="income">{{ currency(summary.income) }}</strong></article>
        <article class="metric"><span>Gastos</span><strong class="expense">{{ currency(summary.expense) }}</strong></article>
        <article class="metric"><span>Balance</span><strong [class.income]="summary.balance >= 0" [class.expense]="summary.balance < 0">{{ currency(summary.balance) }}</strong></article>
      </section>

      <section class="panel" *ngIf="showTransactionForm">
        <h2>Nueva transaccion</h2>
        <div class="form-grid">
          <label><span>Tipo</span><select [(ngModel)]="transactionType"><option value="income">Ingreso</option><option value="expense">Gasto</option></select></label>
          <label><span>Monto</span><input [(ngModel)]="transactionAmount" type="number" min="1"></label>
          <label><span>Categoria</span><input [(ngModel)]="transactionCategory"></label>
          <label><span>Fecha</span><input [(ngModel)]="transactionDate" type="date"></label>
          <label class="full"><span>Descripcion</span><textarea [(ngModel)]="transactionDescription" rows="3"></textarea></label>
        </div>
        <div class="actions"><button class="btn" type="button" (click)="addTransaction()">Guardar</button></div>
      </section>

      <section class="panel" *ngIf="showTemplateForm">
        <h2>Gasto predeterminado</h2>
        <div class="form-grid">
          <label><span>Categoria</span><input [(ngModel)]="templateCategory"></label>
          <label><span>Monto</span><input [(ngModel)]="templateAmount" type="number" min="1"></label>
          <label class="full"><span>Descripcion</span><textarea [(ngModel)]="templateDescription" rows="3"></textarea></label>
        </div>
        <div class="actions"><button class="btn" type="button" (click)="addTemplate()">Guardar</button></div>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Gastos predeterminados</h2><p class="muted">Aplicalos rapido al flujo del establecimiento.</p></div>
        <div class="stack" *ngIf="templates.length; else noTemplates">
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
        <div class="stack" *ngIf="transactions.length; else noTransactions">
          <article class="row-card" *ngFor="let item of transactions">
            <div>
              <strong>{{ item.category }}</strong>
              <p class="muted">{{ item.description }}</p>
              <small>{{ item.date | date:'mediumDate' }}</small>
            </div>
            <div class="actions">
              <span class="amount" [class.income]="item.type === 'income'" [class.expense]="item.type === 'expense'">{{ currency(item.amount) }}</span>
              <button class="btn ghost" type="button" (click)="deleteTransaction(item.id)">Eliminar</button>
            </div>
          </article>
        </div>
        <ng-template #noTransactions><p class="muted">No hay movimientos registrados.</p></ng-template>
      </section>
    </div>
  `,
  styles: [`
    .shell { padding: 32px; display: grid; gap: 24px; }
    .topbar, .panel, .metric { background: var(--surface); border: 1px solid var(--surface-border); border-radius: 28px; box-shadow: var(--shadow-card); backdrop-filter: blur(12px); }
    .topbar, .panel, .row-card { padding: 24px; }
    .topbar { display: flex; justify-content: space-between; gap: 20px; background: linear-gradient(135deg, rgba(23,58,99,.92), rgba(63,124,191,.84)); color: #fff; }
    .metrics { display:grid; gap:16px; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
    .metric { padding: 22px; display:grid; gap:8px; background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(248,250,255,.82)); }
    .actions { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
    .btn { border:0; border-radius:999px; padding:12px 18px; background:linear-gradient(135deg, #173a63, #3f7cbf); color:#fff; cursor:pointer; font-weight:700; text-decoration:none; }
    .ghost { background:linear-gradient(135deg, rgba(23,58,99,.06), rgba(63,124,191,.14)); color:#23407a; border:1px solid rgba(74, 102, 158, .12); }
    .back { text-decoration:none; color:inherit; font-weight:700; }
    .muted, small { color:#66728a; }
    .topbar .muted { color:rgba(255,255,255,.78); }
    .form-grid { display:grid; gap:14px; grid-template-columns: repeat(2, minmax(0,1fr)); }
    .full { grid-column:1 / -1; }
    label { display:grid; gap:8px; }
    input, textarea, select { width:100%; border:1px solid rgba(100, 126, 176, .22); border-radius:18px; padding:14px 16px; background:linear-gradient(180deg, #ffffff, #f7faff); }
    .stack { display:grid; gap:12px; }
    .row-card { display:flex; justify-content:space-between; gap:16px; border-radius:20px; background:linear-gradient(135deg, rgba(23,58,99,.05), rgba(217,141,67,.08)); border:1px solid rgba(92, 117, 161, .12); }
    .amount { font-weight:700; }
    .income { color:#12976b; }
    .expense { color:#d24f45; }
    @media (max-width: 768px) { .shell { padding: 18px; } .topbar, .row-card { flex-direction:column; } .form-grid { grid-template-columns:1fr; } }
  `],
})
export class EstablishmentDetailPageComponent implements OnInit {
  establishment: Establishment | undefined;
  transactions: Transaction[] = [];
  templates: ExpenseTemplate[] = [];
  summary = { month: new Date().toISOString().slice(0, 7), income: 0, expense: 0, balance: 0 };
  establishmentId = '';
  showTransactionForm = false;
  showTemplateForm = false;
  transactionType: 'income' | 'expense' = 'expense';
  transactionAmount = 0;
  transactionCategory = '';
  transactionDescription = '';
  transactionDate = new Date().toISOString().slice(0, 10);
  templateCategory = '';
  templateDescription = '';
  templateAmount = 0;

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

    this.transactions = await this.storage.getTransactionsByEstablishment(this.establishmentId);
    this.templates = await this.storage.getExpenseTemplatesByEstablishment(this.establishmentId);
    this.summary = {
      month,
      income: this.establishment.income ?? 0,
      expense: this.establishment.expense ?? 0,
      balance: this.establishment.balance ?? 0,
    };
  }

  async addTransaction(): Promise<void> {
    if (!this.establishment || !this.transactionCategory.trim() || this.transactionAmount <= 0) {
      return;
    }

    await this.storage.saveTransaction({
      establishmentId: this.establishment.id,
      type: this.transactionType,
      amount: this.transactionAmount,
      category: this.transactionCategory,
      description: this.transactionDescription,
      date: new Date(this.transactionDate).toISOString(),
    });

    this.showTransactionForm = false;
    this.transactionAmount = 0;
    this.transactionCategory = '';
    this.transactionDescription = '';
    await this.refresh();
  }

  async addTemplate(): Promise<void> {
    if (!this.establishment || !this.templateCategory.trim() || this.templateAmount <= 0) {
      return;
    }

    await this.storage.saveExpenseTemplate({
      establishmentId: this.establishment.id,
      category: this.templateCategory,
      description: this.templateDescription,
      amount: this.templateAmount,
    });

    this.showTemplateForm = false;
    this.templateCategory = '';
    this.templateDescription = '';
    this.templateAmount = 0;
    await this.refresh();
  }

  async applyTemplate(item: ExpenseTemplate): Promise<void> {
    await this.storage.applyExpenseTemplate(item);
    await this.refresh();
  }

  async deleteTemplate(id: string): Promise<void> {
    await this.storage.deleteExpenseTemplate(id);
    await this.refresh();
  }

  async deleteTransaction(id: string): Promise<void> {
    await this.storage.deleteTransaction(id);
    await this.refresh();
  }

  currency(value: number): string {
    return this.summaryService.formatCurrency(value);
  }
}
