import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { Company } from '../models';
import { AuthService } from '../services/auth.service';
import { StorageService } from '../services/storage.service';

@Component({
  selector: 'app-companies-page',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  template: `
    <div class="shell" *ngIf="auth.can('manage-companies'); else blocked">
      <header class="topbar">
        <div>
          <a routerLink="/" class="back">Volver</a>
          <h1>Empresas</h1>
          <p class="muted">Mantenimiento exclusivo para superusuario.</p>
        </div>
      </header>

      <section class="panel">
        <div class="panel-head"><h2>Nueva empresa</h2></div>
        <div class="form-grid">
          <label><span>Empresa</span><input [(ngModel)]="name"></label>
          <label><span>Descripcion</span><input [(ngModel)]="description"></label>
          <label><span>Admin nombre</span><input [(ngModel)]="adminName"></label>
          <label><span>Admin email</span><input [(ngModel)]="adminEmail" type="email"></label>
          <label class="full"><span>Admin contrasena</span><input [(ngModel)]="adminPassword" type="password"></label>
        </div>
        <div class="actions"><button class="btn" type="button" (click)="save()">Crear empresa</button></div>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Empresas creadas</h2></div>
        <div class="company-grid">
          <article class="company-card" *ngFor="let item of companies">
            <div>
              <strong>{{ item.name }}</strong>
              <p class="muted">{{ item.description || 'Sin descripcion' }}</p>
            </div>
            <div class="stats">
              <div>
                <span>Establecimientos</span>
                <strong>{{ item.establishmentsCount ?? 0 }}</strong>
              </div>
              <div>
                <span>Usuarios</span>
                <strong>{{ item.usersCount ?? 0 }}</strong>
              </div>
            </div>
            <div class="pill-row">
              <span class="pill">{{ item.planName || 'Plan base' }}</span>
              <span class="pill alt">{{ item.subscriptionStatus || 'Sin estado' }}</span>
              <span class="pill soft">{{ item.currencyCode || 'COP' }}</span>
            </div>
            <div class="meta">
              <a class="btn small" [routerLink]="['/empresas', item.id]">Entrar al detalle</a>
            </div>
          </article>
        </div>
      </section>
    </div>

    <ng-template #blocked>
      <div class="shell"><section class="panel"><h1>Sin acceso</h1><p class="muted">Solo el superusuario puede administrar empresas.</p></section></div>
    </ng-template>
  `,
  styles: [`
    .shell { padding:32px; display:grid; gap:24px; }
    .topbar, .panel { background:var(--surface); border:1px solid var(--surface-border); border-radius:28px; box-shadow:var(--shadow-card); backdrop-filter: blur(14px); }
    .topbar, .panel, .company-card { padding:24px; }
    .topbar { background:linear-gradient(135deg, rgba(15,23,42,.97), rgba(30,58,95,.94) 48%, rgba(47,110,165,.90)); color:#fff; position:relative; overflow:hidden; }
    .topbar::after { content:""; position:absolute; width:220px; height:220px; right:-60px; bottom:-110px; border-radius:999px; background:radial-gradient(circle, rgba(244,162,97,.30), transparent 70%); pointer-events:none; }
    .panel { display:grid; gap:18px; background:var(--surface-strong); }
    .form-grid { display:grid; gap:14px; grid-template-columns: repeat(2, minmax(0,1fr)); }
    .full { grid-column:1 / -1; }
    .actions, .meta { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
    .company-grid { display:grid; gap:16px; grid-template-columns:repeat(auto-fit, minmax(320px, 420px)); align-items:start; }
    label { display:grid; gap:8px; }
    input { width:100%; border:1px solid rgba(71, 85, 105, .18); border-radius:18px; padding:14px 16px; font:inherit; background:linear-gradient(180deg, #ffffff, #f8fafc); }
    .btn { border:0; border-radius:999px; padding:12px 18px; background:linear-gradient(135deg, #0f172a, #2f6ea5); color:#fff; cursor:pointer; font-weight:700; text-decoration:none; box-shadow:0 14px 28px rgba(15,23,42,.18); }
    .btn.small { padding:10px 14px; font-size:13px; }
    .company-card { display:grid; gap:18px; border-radius:24px; background:linear-gradient(180deg, rgba(255,255,255,.98), rgba(241,245,249,.90)); border:1px solid rgba(71, 85, 105, .12); box-shadow:var(--shadow-card); }
    .stats { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:12px; padding:14px 16px; border-radius:18px; background:linear-gradient(135deg, rgba(15,23,42,.04), rgba(244,162,97,.10)); border:1px solid rgba(71, 85, 105, .10); }
    .stats div { display:grid; gap:6px; }
    .stats span { color:var(--muted); font-size:13px; }
    .stats strong { font-size:28px; letter-spacing:-.04em; color:var(--ink); }
    .pill-row { display:flex; gap:8px; flex-wrap:wrap; }
    .pill { display:inline-flex; align-items:center; padding:8px 12px; border-radius:999px; background:linear-gradient(135deg, rgba(47,110,165,.12), rgba(106,166,217,.24)); color:#24466b; font-size:12px; font-weight:700; }
    .pill.alt { background:linear-gradient(135deg, rgba(244,162,97,.16), rgba(255,255,255,.55)); color:#84522a; }
    .pill.soft { background:linear-gradient(135deg, rgba(15,23,42,.05), rgba(241,245,249,.92)); color:#334155; }
    .back { text-decoration:none; color:inherit; font-weight:700; }
    .muted, .meta span { color:var(--muted); }
    @media (max-width: 768px) { .shell { padding:18px; } .form-grid { grid-template-columns:1fr; } .company-grid { grid-template-columns:1fr; } .stats { grid-template-columns:1fr; } }
  `],
})
export class CompaniesPageComponent implements OnInit {
  companies: Company[] = [];
  name = '';
  description = '';
  adminName = '';
  adminEmail = '';
  adminPassword = '';

  constructor(public auth: AuthService, private readonly storage: StorageService, private readonly router: Router) {
    if (!this.auth.can('manage-companies')) {
      void this.router.navigate(['/']);
    }
  }

  async ngOnInit(): Promise<void> {
    await this.refresh();
  }

  async refresh(): Promise<void> {
    this.companies = await this.storage.getCompanies();
  }

  async save(): Promise<void> {
    if (!this.name.trim() || !this.adminName.trim() || !this.adminEmail.trim() || !this.adminPassword.trim()) {
      return;
    }

    await this.storage.saveCompany({
      name: this.name,
      description: this.description,
      adminName: this.adminName,
      adminEmail: this.adminEmail,
      adminPassword: this.adminPassword,
    });

    this.name = '';
    this.description = '';
    this.adminName = '';
    this.adminEmail = '';
    this.adminPassword = '';
    await this.refresh();
  }
}
