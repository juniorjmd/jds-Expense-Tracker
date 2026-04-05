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
            <button class="btn" type="button" (click)="showTransactionForm = !showTransactionForm">
              {{ showTransactionForm ? 'Cancelar ingreso/gasto' : 'Nueva transaccion' }}
            </button>
            <button class="btn ghost" type="button" (click)="showTemplateForm = !showTemplateForm">
              {{ showTemplateForm ? 'Cancelar predeterminado' : 'Gasto predeterminado' }}
            </button>
          </div>
        </div>
      </header>

      <section class="grid metrics">
        <article class="metric">
          <span>Ingresos</span>
          <strong class="income">{{ currency(summary.income) }}</strong>
          <small>Entradas registradas en este establecimiento.</small>
        </article>
        <article class="metric">
          <span>Gastos</span>
          <strong class="expense">{{ currency(summary.expense) }}</strong>
          <small>Salidas asociadas a la operacion local.</small>
        </article>
        <article class="metric">
          <span>Balance</span>
          <strong [class.income]="summary.balance >= 0" [class.expense]="summary.balance < 0">{{ currency(summary.balance) }}</strong>
          <small>Resultado acumulado para el periodo activo.</small>
        </article>
      </section>

      <section class="panel" *ngIf="showTransactionForm">
        <div class="panel-head">
          <h2>Nueva transaccion</h2>
          <p class="muted">Registra ingresos o gastos sin salir del contexto del establecimiento.</p>
        </div>
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
        <div class="panel-head">
          <h2>Gasto predeterminado</h2>
          <p class="muted">Guarda gastos recurrentes para aplicarlos rapido cuando sea necesario.</p>
        </div>
        <div class="form-grid">
          <label><span>Categoria</span><input [(ngModel)]="templateCategory"></label>
          <label><span>Monto</span><input [(ngModel)]="templateAmount" type="number" min="1"></label>
          <label class="full"><span>Descripcion</span><textarea [(ngModel)]="templateDescription" rows="3"></textarea></label>
        </div>
        <div class="actions"><button class="btn" type="button" (click)="addTemplate()">Guardar</button></div>
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
    .hero, .panel, .metric, .row-card { background: var(--surface); border: 1px solid var(--surface-border); border-radius: 28px; box-shadow: var(--shadow-card); backdrop-filter: blur(14px); }
    .hero { padding: 30px 32px; display: flex; justify-content: space-between; gap: 20px; background: linear-gradient(135deg, rgba(15,23,42,.97), rgba(30,58,95,.94) 48%, rgba(47,110,165,.90)); color: #fff; position: relative; overflow: hidden; }
    .hero::after { content: ""; position: absolute; inset: auto -6% -38% auto; width: 240px; height: 240px; border-radius: 999px; background: radial-gradient(circle, rgba(244,162,97,.30), transparent 68%); pointer-events: none; }
    .hero-copy { display: grid; gap: 8px; max-width: 640px; }
    .hero-note { max-width: 360px; display: grid; gap: 8px; padding: 18px; border-radius: 22px; background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.16); align-content: start; }
    .eyebrow { margin: 8px 0 0; text-transform: uppercase; letter-spacing: .2em; font-size: 11px; font-weight: 800; color: rgba(255,255,255,.7); }
    .metrics { display:grid; gap:16px; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
    .metric { padding: 22px; display:grid; gap:8px; background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(241,245,249,.90)); }
    .metric strong { font-size: 28px; letter-spacing: -.04em; }
    .panel { padding: 24px; display: grid; gap: 18px; background: var(--surface-strong); }
    .panel-head { display: grid; gap: 6px; }
    .actions { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
    .btn { border:0; border-radius:999px; padding:12px 18px; background:linear-gradient(135deg, #0f172a, #2f6ea5); color:#fff; cursor:pointer; font-weight:700; text-decoration:none; box-shadow:0 16px 30px rgba(15, 23, 42, .20); }
    .ghost { background:linear-gradient(135deg, rgba(255,255,255,.14), rgba(255,255,255,.08)); color:#fff; border:1px solid rgba(255,255,255,.18); box-shadow:none; }
    .panel .ghost { background:linear-gradient(135deg, rgba(47,110,165,.12), rgba(106,166,217,.22)); color:#24466b; border:1px solid rgba(71, 85, 105, .10); }
    .back { text-decoration:none; color:inherit; font-weight:700; }
    .muted, small { color:var(--muted); }
    .hero .muted, .hero-note span { color:rgba(255,255,255,.78); }
    h1, h2, p { margin: 0; }
    .form-grid { display:grid; gap:14px; grid-template-columns: repeat(2, minmax(0,1fr)); }
    .full { grid-column:1 / -1; }
    label { display:grid; gap:8px; }
    input, textarea, select { width:100%; border:1px solid rgba(71, 85, 105, .18); border-radius:18px; padding:14px 16px; background:linear-gradient(180deg, #ffffff, #f8fafc); }
    .list-grid { display:grid; gap:12px; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
    .row-card { padding: 20px; display:flex; justify-content:space-between; gap:16px; border-radius:22px; background:linear-gradient(180deg, rgba(255,255,255,.98), rgba(241,245,249,.90)); align-items:flex-start; }
    .amount { font-weight:700; }
    .income { color:var(--success); }
    .expense { color:var(--danger); }
    @media (max-width: 920px) { .shell { padding: 18px; } .hero, .row-card { flex-direction:column; } .form-grid { grid-template-columns:1fr; } .list-grid { grid-template-columns:1fr; } }
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
