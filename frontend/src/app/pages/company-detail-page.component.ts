import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { CompanyOverview } from '../models';
import { AuthService } from '../services/auth.service';
import { StorageService } from '../services/storage.service';
import { SummaryService } from '../services/summary.service';

@Component({
  selector: 'app-company-detail-page',
  standalone: true,
  imports: [CommonModule, RouterLink],
  template: `
    <div class="shell" *ngIf="auth.can('manage-companies'); else blocked">
      <header class="hero" *ngIf="overview">
        <div>
          <a routerLink="/empresas" class="back">Volver a empresas</a>
          <p class="eyebrow">Acceso trazado</p>
          <h1>{{ overview.company.name }}</h1>
          <p class="muted">{{ overview.company.description || 'Sin descripcion registrada.' }}</p>
        </div>
        <div class="hero-note">
          <strong>Seguimiento activo</strong>
          <span>Cada ingreso al detalle de esta empresa queda registrado para auditoria.</span>
        </div>
      </header>

      <section class="grid metrics" *ngIf="overview">
        <article class="metric">
          <span>Usuarios</span>
          <strong>{{ overview.company.usersCount ?? overview.users.length }}</strong>
        </article>
        <article class="metric">
          <span>Establecimientos</span>
          <strong>{{ overview.company.establishmentsCount ?? overview.establishments.length }}</strong>
        </article>
        <article class="metric">
          <span>Ingresos del mes</span>
          <strong class="income">{{ currency(overview.summary.income) }}</strong>
        </article>
        <article class="metric">
          <span>Balance</span>
          <strong [class.income]="overview.summary.balance >= 0" [class.expense]="overview.summary.balance < 0">
            {{ currency(overview.summary.balance) }}
          </strong>
        </article>
      </section>

      <section class="panel" *ngIf="overview">
        <div class="panel-head">
          <h2>Contexto SaaS</h2>
          <p class="muted">Base inicial para planes, configuracion y operacion por empresa.</p>
        </div>
        <div class="context-grid">
          <article class="context-card">
            <span>Plan actual</span>
            <strong>{{ overview.subscription?.planName || overview.company.planName || 'Plan base' }}</strong>
            <small>{{ overview.subscription?.status || overview.company.subscriptionStatus || 'Sin estado' }}</small>
          </article>
          <article class="context-card">
            <span>Moneda</span>
            <strong>{{ overview.settings?.currencyCode || overview.company.currencyCode || 'COP' }}</strong>
            <small>{{ overview.settings?.timezone || overview.company.timezone || 'America/Bogota' }}</small>
          </article>
          <article class="context-card">
            <span>Branding</span>
            <strong>{{ overview.settings?.brandingName || overview.company.brandingName || overview.company.name }}</strong>
            <small>{{ overview.settings?.dateFormat || overview.company.dateFormat || 'Y-m-d' }}</small>
          </article>
        </div>
      </section>

      <section class="panel" *ngIf="overview">
        <div class="panel-head">
          <h2>Establecimientos de la empresa</h2>
          <p class="muted">Desde aqui decides si quieres profundizar en el detalle de una sucursal especifica.</p>
        </div>
        <div class="grid cards">
          <article class="card" *ngFor="let item of overview.establishments">
            <div>
              <h3>{{ item.name }}</h3>
              <p class="muted">{{ item.description || 'Sin descripcion' }}</p>
            </div>
            <div class="stats-line">
              <span>{{ item.transactionCount ?? 0 }} movimientos</span>
              <strong>{{ currency(item.balance ?? 0) }}</strong>
            </div>
            <a class="btn" [routerLink]="['/establecimiento', item.id]">Ver establecimiento</a>
          </article>
        </div>
      </section>

      <section class="grid split" *ngIf="overview">
        <section class="panel">
          <div class="panel-head">
            <h2>Usuarios de la empresa</h2>
            <p class="muted">Solo se muestran usuarios pertenecientes a esta empresa.</p>
          </div>
          <div class="list-grid">
            <article class="row-card" *ngFor="let user of overview.users">
              <div>
                <strong>{{ user.name }}</strong>
                <p class="muted">{{ user.email }}</p>
              </div>
              <span class="badge">{{ user.role }}</span>
            </article>
          </div>
        </section>

        <section class="panel">
          <div class="panel-head">
            <h2>Seguimiento de accesos</h2>
            <p class="muted">Auditoria de ingresos explicitos al detalle de esta empresa.</p>
          </div>
          <div class="list-grid">
            <article class="row-card" *ngFor="let log of overview.accessLogs">
              <div>
                <strong>{{ log.actorName }}</strong>
                <p class="muted">{{ log.actorEmail }}</p>
                <p class="muted">{{ log.note || log.action }}</p>
              </div>
              <span class="badge">{{ formatDate(log.createdAt) }}</span>
            </article>
          </div>
        </section>
      </section>

      <section class="panel" *ngIf="overview">
        <div class="panel-head">
          <h2>Actividad critica reciente</h2>
          <p class="muted">Creaciones, eliminaciones y operaciones sensibles dentro de esta empresa.</p>
        </div>
        <div class="list-grid">
          <article class="row-card" *ngFor="let log of overview.activityLogs">
            <div>
              <strong>{{ log.actorName }}</strong>
              <p class="muted">{{ log.note || log.action }}</p>
              <p class="muted">{{ log.entityType }} #{{ log.entityId }}</p>
            </div>
            <span class="badge">{{ formatDate(log.createdAt) }}</span>
          </article>
        </div>
      </section>
    </div>

    <ng-template #blocked>
      <div class="shell"><section class="panel"><h1>Sin acceso</h1><p class="muted">Solo el superusuario puede ingresar al detalle de una empresa.</p></section></div>
    </ng-template>
  `,
  styles: [`
    .shell { padding: 32px; display: grid; gap: 24px; }
    .hero, .panel, .metric, .card, .row-card { background: var(--surface); border: 1px solid var(--surface-border); border-radius: 28px; box-shadow: var(--shadow-card); backdrop-filter: blur(14px); }
    .hero { padding: 30px 32px; display: flex; justify-content: space-between; gap: 20px; background: linear-gradient(135deg, rgba(15,23,42,.97), rgba(30,58,95,.94) 48%, rgba(47,110,165,.90)); color: #fff; position: relative; overflow: hidden; }
    .hero::after { content: ""; position: absolute; inset: auto -6% -35% auto; width: 240px; height: 240px; border-radius: 999px; background: radial-gradient(circle, rgba(244,162,97,.32), transparent 68%); pointer-events: none; }
    .hero-note { max-width: 320px; display: grid; gap: 6px; padding: 18px; border-radius: 22px; background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.16); }
    .back { color: inherit; text-decoration: none; font-weight: 700; }
    .eyebrow { margin: 10px 0 6px; text-transform: uppercase; letter-spacing: .2em; font-size: 11px; font-weight: 800; color: rgba(255,255,255,.7); }
    .muted { color: var(--muted); }
    .hero .muted { color: rgba(255,255,255,.76); }
    h1, h2, h3, p { margin: 0; }
    .grid { display: grid; gap: 18px; }
    .metrics { grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); }
    .metric { padding: 22px; display: grid; gap: 10px; background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(241,245,249,.90)); }
    .metric strong { font-size: 28px; letter-spacing: -.04em; }
    .panel { padding: 24px; display: grid; gap: 18px; background: var(--surface-strong); }
    .panel-head { display: grid; gap: 6px; }
    .context-grid { display:grid; gap:14px; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); }
    .context-card { padding:18px; border-radius:22px; background:linear-gradient(135deg, rgba(15,23,42,.04), rgba(244,162,97,.10)); border:1px solid rgba(71,85,105,.10); display:grid; gap:6px; }
    .context-card span, .context-card small { color:var(--muted); }
    .context-card strong { font-size:22px; letter-spacing:-.03em; color:var(--ink); }
    .cards { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
    .card, .row-card { padding: 20px; display: grid; gap: 14px; background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(241,245,249,.90)); }
    .stats-line { display: flex; justify-content: space-between; gap: 14px; color: #475569; }
    .split { grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; }
    .list-grid { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); align-items: start; }
    .row-card { display: flex; justify-content: space-between; align-items: start; gap: 16px; border-radius: 22px; }
    .badge { display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; border-radius: 999px; background: linear-gradient(135deg, rgba(47,110,165,.12), rgba(106,166,217,.24)); color: #24466b; font-size: 12px; font-weight: 700; }
    .btn { border: 0; border-radius: 999px; padding: 12px 18px; background: linear-gradient(135deg, #0f172a, #2f6ea5); color: #fff; text-decoration: none; cursor: pointer; font-weight: 700; width: fit-content; box-shadow: 0 16px 30px rgba(15, 23, 42, .20); }
    .income { color: var(--success); }
    .expense { color: var(--danger); }
    @media (max-width: 920px) { .shell { padding: 18px; } .hero, .row-card { flex-direction: column; } .split { grid-template-columns: 1fr; } }
  `],
})
export class CompanyDetailPageComponent implements OnInit {
  overview: CompanyOverview | null = null;

  constructor(
    public auth: AuthService,
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly storage: StorageService,
    private readonly summaryService: SummaryService
  ) {}

  async ngOnInit(): Promise<void> {
    if (!this.auth.can('manage-companies')) {
      await this.router.navigate(['/']);
      return;
    }

    const companyId = this.route.snapshot.paramMap.get('id');
    if (!companyId) {
      await this.router.navigate(['/empresas']);
      return;
    }

    this.overview = await this.storage.getCompanyOverview(companyId);
  }

  currency(value: number): string {
    return this.summaryService.formatCurrency(value);
  }

  formatDate(value: string): string {
    return new Intl.DateTimeFormat('es-CO', {
      dateStyle: 'medium',
      timeStyle: 'short',
    }).format(new Date(value));
  }
}
