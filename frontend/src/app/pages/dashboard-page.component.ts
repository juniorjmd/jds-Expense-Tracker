import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { Establishment } from '../models';
import { AuthService } from '../services/auth.service';
import { StorageService } from '../services/storage.service';
import { SummaryService } from '../services/summary.service';

@Component({
  selector: 'app-dashboard-page',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  template: `
    <div class="shell">
      <header class="topbar">
        <div>
          <p class="eyebrow">Gestion financiera</p>
          <h1>Panel general</h1>
          <p class="muted">Controla ingresos, gastos y establecimientos desde una sola vista.</p>
        </div>

        <div class="actions">
          <a *ngIf="auth.can('view-summary')" routerLink="/resumen" class="btn ghost">Resumen</a>
          <a *ngIf="auth.can('manage-users')" routerLink="/usuarios" class="btn ghost">Usuarios</a>
          <button *ngIf="auth.can('create')" class="btn" type="button" (click)="toggleEstablishmentForm()">
            {{ showEstablishmentForm ? 'Cancelar' : 'Nuevo establecimiento' }}
          </button>
          <button class="btn ghost" type="button" (click)="logout()">Salir</button>
        </div>
      </header>

      <section class="grid metrics">
        <article class="metric">
          <span>Establecimientos</span>
          <strong>{{ establishments.length }}</strong>
        </article>
        <article class="metric">
          <span>Ingresos del mes</span>
          <strong class="income">{{ currency(summary.income) }}</strong>
        </article>
        <article class="metric">
          <span>Gastos del mes</span>
          <strong class="expense">{{ currency(summary.expense) }}</strong>
        </article>
        <article class="metric">
          <span>Balance</span>
          <strong [class.income]="summary.balance >= 0" [class.expense]="summary.balance < 0">
            {{ currency(summary.balance) }}
          </strong>
        </article>
      </section>

      <section *ngIf="showEstablishmentForm" class="panel form-panel">
        <div class="panel-head">
          <h2>Nuevo establecimiento</h2>
          <p class="muted">Portado del flujo principal del Figma a una estructura Angular real.</p>
        </div>

        <div class="form-grid">
          <label>
            <span>Nombre</span>
            <input [(ngModel)]="establishmentName" placeholder="Ej. Sucursal Centro">
          </label>
          <label class="full">
            <span>Descripcion</span>
            <textarea [(ngModel)]="establishmentDescription" rows="3" placeholder="Describe el establecimiento"></textarea>
          </label>
        </div>

        <div class="panel-actions">
          <button class="btn" type="button" (click)="createEstablishment()">Guardar</button>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head">
          <h2>Mis establecimientos</h2>
          <p class="muted">Cada tarjeta concentra ingresos, gastos y acceso rapido al detalle.</p>
        </div>

        <div *ngIf="establishments.length; else emptyState" class="grid cards">
          <article class="card" *ngFor="let item of establishments">
            <div class="card-head">
              <div>
                <h3>{{ item.name }}</h3>
                <p class="muted">{{ item.description || 'Sin descripcion' }}</p>
              </div>
              <span class="badge">{{ item.transactionCount ?? 0 }} mov.</span>
            </div>

            <div class="mini-stats">
              <div>
                <span>Ingresos</span>
                <strong class="income">{{ currency(itemSummary(item.id).income) }}</strong>
              </div>
              <div>
                <span>Gastos</span>
                <strong class="expense">{{ currency(itemSummary(item.id).expense) }}</strong>
              </div>
            </div>

            <div class="card-actions">
              <a class="btn" [routerLink]="['/establecimiento', item.id]">Ver detalle</a>
              <button class="btn ghost" type="button" (click)="deleteEstablishment(item.id)">Eliminar</button>
            </div>
          </article>
        </div>

        <ng-template #emptyState>
          <div class="empty">
            <h3>Aun no hay establecimientos</h3>
            <p class="muted">Crea el primero para empezar a registrar movimientos.</p>
          </div>
        </ng-template>
      </section>
    </div>
  `,
  styles: [`
    .shell { padding: 32px; display: grid; gap: 24px; }
    .topbar, .panel, .metric, .card { background: #fff; border-radius: 24px; box-shadow: 0 18px 50px rgba(18, 30, 61, .08); }
    .topbar { padding: 28px 32px; display: flex; justify-content: space-between; gap: 24px; align-items: flex-start; }
    .actions, .panel-actions, .card-actions { display: flex; gap: 12px; flex-wrap: wrap; }
    .eyebrow { text-transform: uppercase; letter-spacing: .18em; margin: 0 0 10px; font-size: 12px; font-weight: 700; color: #6e7c97; }
    h1, h2, h3, p { margin: 0; }
    .muted { color: #66728a; }
    .grid { display: grid; gap: 18px; }
    .metrics { grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); }
    .metric { padding: 22px; display: grid; gap: 10px; }
    .metric span, .mini-stats span, .badge { color: #6b7790; font-size: 13px; }
    .metric strong { font-size: 28px; }
    .panel { padding: 24px; display: grid; gap: 18px; }
    .panel-head { display: grid; gap: 6px; }
    .form-grid { display: grid; gap: 14px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .form-grid .full { grid-column: 1 / -1; }
    label { display: grid; gap: 8px; color: #30405d; font-weight: 600; }
    input, textarea, select { width: 100%; border: 1px solid #d8dfec; border-radius: 16px; padding: 14px 16px; font: inherit; background: #f9fbff; }
    .cards { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
    .card { padding: 22px; display: grid; gap: 18px; }
    .card-head, .mini-stats { display: flex; justify-content: space-between; gap: 16px; }
    .mini-stats { padding: 16px; border-radius: 18px; background: #f5f7fb; }
    .mini-stats div { display: grid; gap: 6px; }
    .badge { background: #eef3ff; color: #45629f; border-radius: 999px; padding: 8px 12px; font-weight: 700; height: fit-content; }
    .btn { border: 0; border-radius: 999px; padding: 12px 18px; background: #14213d; color: #fff; text-decoration: none; cursor: pointer; font: inherit; }
    .btn.ghost { background: #eef3ff; color: #23407a; }
    .income { color: #12976b; }
    .expense { color: #d24f45; }
    .empty { padding: 20px 0 4px; display: grid; gap: 8px; }
    @media (max-width: 768px) { .shell { padding: 18px; } .topbar { flex-direction: column; } .form-grid { grid-template-columns: 1fr; } }
  `],
})
export class DashboardPageComponent implements OnInit {
  establishments: Establishment[] = [];
  showEstablishmentForm = false;
  establishmentName = '';
  establishmentDescription = '';
  summary = { month: new Date().toISOString().slice(0, 7), income: 0, expense: 0, balance: 0 };

  constructor(
    public auth: AuthService,
    private readonly storage: StorageService,
    private readonly summaryService: SummaryService,
    private readonly router: Router
  ) {}

  async ngOnInit(): Promise<void> {
    await this.refresh();
  }

  async refresh(): Promise<void> {
    const month = new Date().toISOString().slice(0, 7);
    this.establishments = (await this.storage.getEstablishments(month))
      .filter((item) => this.auth.canAccessEstablishment(item.id));
    this.summary = await this.storage.getSummary(month);
  }

  toggleEstablishmentForm(): void {
    this.showEstablishmentForm = !this.showEstablishmentForm;
  }

  async createEstablishment(): Promise<void> {
    if (!this.establishmentName.trim()) {
      return;
    }

    await this.storage.saveEstablishment({ name: this.establishmentName, description: this.establishmentDescription });
    this.establishmentName = '';
    this.establishmentDescription = '';
    this.showEstablishmentForm = false;
    await this.refresh();
  }

  async deleteEstablishment(id: string): Promise<void> {
    await this.storage.deleteEstablishment(id);
    await this.refresh();
  }

  itemSummary(id: string) {
    const item = this.establishments.find((establishment) => establishment.id === id);
    return {
      income: item?.income ?? 0,
      expense: item?.expense ?? 0,
    };
  }

  currency(value: number): string {
    return this.summaryService.formatCurrency(value);
  }

  async logout(): Promise<void> {
    this.auth.logout();
    await this.router.navigate(['/login']);
  }
}
