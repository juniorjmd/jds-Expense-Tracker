import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { Company, Establishment } from '../models';
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
          <a *ngIf="auth.can('manage-companies')" routerLink="/empresas" class="btn ghost">Empresas</a>
          <a *ngIf="auth.can('view-summary') && auth.getCurrentUser()?.role !== 'superusuario'" routerLink="/resumen" class="btn ghost">Resumen</a>
          <a *ngIf="auth.can('manage-users')" routerLink="/usuarios" class="btn ghost">Usuarios</a>
          <button *ngIf="auth.can('create')" class="btn" type="button" (click)="toggleEstablishmentForm()">
            {{ showEstablishmentForm ? 'Cancelar' : 'Nuevo establecimiento' }}
          </button>
          <button class="btn ghost" type="button" (click)="logout()">Salir</button>
        </div>
      </header>

      <section class="grid metrics">
        <article class="metric">
          <span>{{ auth.getCurrentUser()?.role === 'superusuario' ? 'Empresas' : 'Establecimientos' }}</span>
          <strong>{{ auth.getCurrentUser()?.role === 'superusuario' ? companies.length : establishments.length }}</strong>
        </article>
        <article class="metric">
          <span>{{ auth.getCurrentUser()?.role === 'superusuario' ? 'Usuarios administradores' : 'Ingresos del mes' }}</span>
          <strong class="income">{{ auth.getCurrentUser()?.role === 'superusuario' ? adminCompaniesCount : currency(summary.income) }}</strong>
        </article>
        <article class="metric">
          <span>{{ auth.getCurrentUser()?.role === 'superusuario' ? 'Seguimiento activo' : 'Gastos del mes' }}</span>
          <strong class="expense">{{ auth.getCurrentUser()?.role === 'superusuario' ? 'Manual' : currency(summary.expense) }}</strong>
        </article>
        <article class="metric">
          <span>{{ auth.getCurrentUser()?.role === 'superusuario' ? 'Politica' : 'Balance' }}</span>
          <strong [class.income]="auth.getCurrentUser()?.role === 'superusuario' || summary.balance >= 0" [class.expense]="auth.getCurrentUser()?.role !== 'superusuario' && summary.balance < 0">
            {{ auth.getCurrentUser()?.role === 'superusuario' ? 'Entrar por empresa' : currency(summary.balance) }}
          </strong>
        </article>
      </section>

      <section *ngIf="auth.getCurrentUser()?.role === 'superusuario'" class="panel super-panel">
        <div class="panel-head">
          <h2>Vista inicial del superusuario</h2>
          <p class="muted">Aqui no mostramos operacion ajena por defecto. Para revisar el detalle de una empresa debes entrar a esa empresa desde mantenimiento, y ese acceso queda registrado.</p>
        </div>

        <div class="grid cards company-cards">
          <article class="card" *ngFor="let item of companies">
            <div class="card-head">
              <div>
                <h3>{{ item.name }}</h3>
                <p class="muted">{{ item.description || 'Sin descripcion' }}</p>
              </div>
              <span class="badge">{{ item.usersCount ?? 0 }} usuarios</span>
            </div>

            <div class="mini-stats">
              <div>
                <span>Establecimientos</span>
                <strong>{{ item.establishmentsCount ?? 0 }}</strong>
              </div>
              <div>
                <span>Accion</span>
                <strong>Ingreso trazado</strong>
              </div>
            </div>

            <div class="card-actions">
              <a class="btn" [routerLink]="['/empresas', item.id]">Entrar a la empresa</a>
            </div>
          </article>
        </div>
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
          <label *ngIf="auth.getCurrentUser()?.role === 'superusuario'">
            <span>Empresa</span>
            <select [(ngModel)]="selectedCompanyId">
              <option value="">Seleccione una empresa</option>
              <option *ngFor="let item of companies" [value]="item.id">{{ item.name }}</option>
            </select>
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

      <section class="panel" *ngIf="auth.getCurrentUser()?.role !== 'superusuario'">
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
    .topbar, .panel, .metric, .card { background: var(--surface); border: 1px solid var(--surface-border); border-radius: 28px; box-shadow: var(--shadow-card); backdrop-filter: blur(14px); }
    .topbar { padding: 30px 32px; display: flex; justify-content: space-between; gap: 24px; align-items: flex-start; background: linear-gradient(135deg, rgba(15,23,42,.97), rgba(30,58,95,.94) 48%, rgba(47,110,165,.90)); color: #fff; box-shadow: 0 30px 72px rgba(15, 23, 42, .20); position: relative; overflow: hidden; }
    .topbar::after { content: ""; position: absolute; inset: auto -10% -35% auto; width: 260px; height: 260px; border-radius: 999px; background: radial-gradient(circle, rgba(244,162,97,.34), transparent 68%); pointer-events: none; }
    .actions, .panel-actions, .card-actions { display: flex; gap: 12px; flex-wrap: wrap; }
    .eyebrow { text-transform: uppercase; letter-spacing: .22em; margin: 0 0 10px; font-size: 11px; font-weight: 800; color: rgba(255,255,255,.70); }
    h1, h2, h3, p { margin: 0; }
    h1 { font-size: clamp(2rem, 4vw, 2.9rem); letter-spacing: -.05em; }
    .topbar .muted { color: rgba(255,255,255,.78); max-width: 42ch; }
    .muted { color: var(--muted); }
    .grid { display: grid; gap: 18px; }
    .metrics { grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); }
    .metric { padding: 22px; display: grid; gap: 10px; position: relative; overflow: hidden; background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(241,245,249,.88)); }
    .metric::after { content: ""; position: absolute; inset: auto 18px 0; height: 5px; border-radius: 999px; background: linear-gradient(90deg, rgba(244,162,97,.95), rgba(47,110,165,.92)); }
    .metric span, .mini-stats span, .badge { color: #64748b; font-size: 13px; }
    .metric strong { font-size: 30px; letter-spacing: -.04em; }
    .panel { padding: 24px; display: grid; gap: 18px; background: var(--surface-strong); }
    .panel-head { display: grid; gap: 6px; }
    .form-grid { display: grid; gap: 14px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .form-grid .full { grid-column: 1 / -1; }
    label { display: grid; gap: 8px; color: #22314d; font-weight: 700; }
    input, textarea, select { width: 100%; border: 1px solid rgba(71, 85, 105, .18); border-radius: 18px; padding: 14px 16px; background: linear-gradient(180deg, #ffffff, #f8fafc); box-shadow: inset 0 1px 0 rgba(255,255,255,.85); }
    input:focus, textarea:focus, select:focus { outline: 2px solid rgba(106,166,217,.22); border-color: #6aa6d9; }
    .cards { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
    .company-cards { grid-template-columns: repeat(auto-fit, minmax(320px, 420px)); align-items: start; }
    .card { padding: 22px; display: grid; gap: 18px; background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(241,245,249,.90)); }
    .card-head, .mini-stats { display: flex; justify-content: space-between; gap: 16px; }
    .mini-stats { padding: 16px; border-radius: 20px; background: linear-gradient(135deg, rgba(15,23,42,.05), rgba(244,162,97,.12)); border: 1px solid rgba(71, 85, 105, .12); }
    .mini-stats div { display: grid; gap: 6px; }
    .badge { background: linear-gradient(135deg, rgba(47,110,165,.12), rgba(106,166,217,.24)); color: #24466b; border-radius: 999px; padding: 8px 12px; font-weight: 700; height: fit-content; }
    .btn { border: 0; border-radius: 999px; padding: 12px 18px; background: linear-gradient(135deg, #0f172a, #2f6ea5); color: #fff; text-decoration: none; cursor: pointer; font-weight: 700; box-shadow: 0 16px 30px rgba(15, 23, 42, .20); }
    .btn.ghost { background: rgba(255,255,255,.16); color: inherit; border: 1px solid rgba(255,255,255,.22); box-shadow: none; }
    .panel .btn.ghost, .card .btn.ghost { background: linear-gradient(135deg, rgba(15,23,42,.05), rgba(106,166,217,.16)); color: #1e3a5f; border: 1px solid rgba(71, 85, 105, .14); }
    .income { color: var(--success); }
    .expense { color: var(--danger); }
    .empty { padding: 20px 0 4px; display: grid; gap: 8px; }
    @media (max-width: 768px) { .shell { padding: 18px; } .topbar { flex-direction: column; } .form-grid { grid-template-columns: 1fr; } }
  `],
})
export class DashboardPageComponent implements OnInit {
  establishments: Establishment[] = [];
  companies: Company[] = [];
  adminCompaniesCount = 0;
  showEstablishmentForm = false;
  establishmentName = '';
  establishmentDescription = '';
  selectedCompanyId = '';
  summary = { month: new Date().toISOString().slice(0, 7), income: 0, expense: 0, balance: 0 };

  constructor(
    public auth: AuthService,
    private readonly storage: StorageService,
    private readonly summaryService: SummaryService,
    private readonly router: Router
  ) {}

  async ngOnInit(): Promise<void> {
    if (this.auth.can('manage-companies')) {
      this.companies = await this.storage.getCompanies();
      this.adminCompaniesCount = this.companies.reduce((total, item) => total + (item.usersCount ?? 0), 0);
    }
    await this.refresh();
  }

  async refresh(): Promise<void> {
    if (this.auth.getCurrentUser()?.role === 'superusuario') {
      this.establishments = [];
      this.summary = { month: new Date().toISOString().slice(0, 7), income: 0, expense: 0, balance: 0 };
      return;
    }

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

    const currentUser = this.auth.getCurrentUser();
    const companyId = currentUser?.role === 'superusuario' ? this.selectedCompanyId : currentUser?.companyId || '';
    if (!companyId) {
      return;
    }

    await this.storage.saveEstablishment({ companyId, name: this.establishmentName, description: this.establishmentDescription });
    this.establishmentName = '';
    this.establishmentDescription = '';
    this.selectedCompanyId = '';
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
