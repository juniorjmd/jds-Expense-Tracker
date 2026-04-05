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
    <div class="shell" *ngIf="auth.can('view-summary'); else blocked">
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
      <div class="shell"><section class="panel"><h1>Sin acceso</h1><p class="muted">Esta vista solo esta disponible para administradores.</p></section></div>
    </ng-template>
  `,
  styles: [`
    .shell { padding:32px; display:grid; gap:24px; }
    .topbar, .panel, .metric { background:#fff; border-radius:24px; box-shadow:0 18px 50px rgba(18,30,61,.08); }
    .topbar { padding:24px; display:flex; justify-content:space-between; gap:20px; align-items:end; }
    .metrics { display:grid; gap:16px; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
    .metric { padding:22px; display:grid; gap:8px; }
    .panel { padding:24px; display:grid; gap:16px; }
    .table { display:grid; gap:10px; }
    .row { display:grid; grid-template-columns: 1.4fr repeat(3, 1fr); gap:12px; padding:16px; background:#f7f9fd; border-radius:18px; }
    .head { background:#eef3ff; font-weight:700; }
    .back { text-decoration:none; color:#42629d; font-weight:700; }
    .muted, span { color:#66728a; }
    .selector { display:grid; gap:8px; }
    select { border:1px solid #d8dfec; border-radius:14px; padding:12px 14px; font:inherit; background:#f9fbff; }
    .income { color:#12976b; }
    .expense { color:#d24f45; }
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
    if (!this.auth.can('view-summary')) {
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
