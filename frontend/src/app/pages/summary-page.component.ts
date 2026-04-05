import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { StorageService } from '../services/storage.service';
import { SummaryService } from '../services/summary.service';

@Component({
  selector: 'app-summary-page',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  template: `
    <div class="shell" *ngIf="auth.can('view-summary') && auth.getCurrentUser()?.role !== 'superusuario'; else blocked">
      <header class="topbar">
        <div>
          <a routerLink="/" class="back">Volver</a>
          <h1>Resumen mensual</h1>
          <p class="muted">Vista concentrada de lo que venia en el flujo de analitica del Figma.</p>
        </div>
        <label class="selector">
          <span>Mes</span>
          <select [(ngModel)]="selectedMonth" (ngModelChange)="refresh()">
            <option *ngFor="let item of months" [value]="item">{{ item }}</option>
          </select>
        </label>
      </header>

      <section class="grid metrics">
        <article class="metric"><span>Ingresos</span><strong class="income">{{ currency(summary.income) }}</strong></article>
        <article class="metric"><span>Gastos</span><strong class="expense">{{ currency(summary.expense) }}</strong></article>
        <article class="metric"><span>Balance</span><strong [class.income]="summary.balance >= 0" [class.expense]="summary.balance < 0">{{ currency(summary.balance) }}</strong></article>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Detalle por establecimiento</h2><p class="muted">Comparativo mensual de ingresos y gastos.</p></div>
        <div class="table">
          <div class="row head"><span>Establecimiento</span><span>Ingresos</span><span>Gastos</span><span>Balance</span></div>
          <div class="row" *ngFor="let row of breakdown">
            <span>{{ row.name }}</span>
            <span class="income">{{ currency(row.income) }}</span>
            <span class="expense">{{ currency(row.expense) }}</span>
            <span [class.income]="row.balance >= 0" [class.expense]="row.balance < 0">{{ currency(row.balance) }}</span>
          </div>
        </div>
      </section>
    </div>

    <ng-template #blocked>
      <div class="shell"><section class="panel"><h1>Sin acceso</h1><p class="muted">El resumen global no se expone al superusuario; para revisar una empresa debes entrar a su detalle.</p></section></div>
    </ng-template>
  `,
  styles: [`
    .shell { padding:32px; display:grid; gap:24px; }
    .topbar, .panel, .metric { background:var(--surface); border:1px solid var(--surface-border); border-radius:28px; box-shadow:var(--shadow-card); backdrop-filter: blur(14px); }
    .topbar { padding:28px; display:flex; justify-content:space-between; gap:20px; align-items:end; background:linear-gradient(135deg, rgba(15,23,42,.97), rgba(30,58,95,.94) 48%, rgba(47,110,165,.90)); color:#fff; position:relative; overflow:hidden; }
    .topbar::after { content:""; position:absolute; width:220px; height:220px; right:-60px; bottom:-110px; border-radius:999px; background:radial-gradient(circle, rgba(244,162,97,.30), transparent 70%); pointer-events:none; }
    .metrics { display:grid; gap:16px; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
    .metric { padding:22px; display:grid; gap:8px; background:linear-gradient(180deg, rgba(255,255,255,.98), rgba(241,245,249,.90)); }
    .panel { padding:24px; display:grid; gap:16px; background:var(--surface-strong); }
    .table { display:grid; gap:10px; }
    .row { display:grid; grid-template-columns: 1.4fr repeat(3, 1fr); gap:12px; padding:16px; background:linear-gradient(135deg, rgba(15,23,42,.04), rgba(244,162,97,.10)); border:1px solid rgba(71, 85, 105, .12); border-radius:20px; }
    .head { background:linear-gradient(135deg, rgba(15,23,42,.08), rgba(106,166,217,.20)); font-weight:700; }
    .back { text-decoration:none; color:inherit; font-weight:700; }
    .topbar .muted { color:rgba(255,255,255,.78); }
    .muted, span { color:var(--muted); }
    .selector { display:grid; gap:8px; }
    select { border:1px solid rgba(255,255,255,.24); border-radius:16px; padding:12px 14px; font:inherit; background:rgba(255,255,255,.16); color:#fff; }
    option { color:#0f172a; }
    .income { color:var(--success); }
    .expense { color:var(--danger); }
    @media (max-width: 768px) { .shell { padding:18px; } .topbar { flex-direction:column; align-items:start; } .row { grid-template-columns:1fr; } }
  `],
})
export class SummaryPageComponent implements OnInit {
  months: string[] = [];
  selectedMonth = '';
  summary = { month: new Date().toISOString().slice(0, 7), income: 0, expense: 0, balance: 0 };
  breakdown: Array<{ name: string; income: number; expense: number; balance: number }> = [];

  constructor(
    public auth: AuthService,
    private readonly storage: StorageService,
    private readonly summaryService: SummaryService,
    private readonly router: Router
  ) {
    if (!this.auth.can('view-summary') || this.auth.getCurrentUser()?.role === 'superusuario') {
      void this.router.navigate(['/']);
    }
  }

  async ngOnInit(): Promise<void> {
    this.selectedMonth = new Date().toISOString().slice(0, 7);
    await this.refresh();
  }

  async refresh(): Promise<void> {
    const summary = await this.storage.getSummary(this.selectedMonth);
    this.summary = summary;
    this.months = summary.months ?? [this.selectedMonth];
    this.breakdown = (summary.breakdown ?? [])
      .filter((item) => this.auth.canAccessEstablishment(item.id))
      .map((item) => ({ name: item.name, income: item.income, expense: item.expense, balance: item.balance }));
  }

  currency(value: number): string {
    return this.summaryService.formatCurrency(value);
  }
}
