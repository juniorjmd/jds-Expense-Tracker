import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

@Component({
  selector: 'app-login-page',
  standalone: true,
  imports: [CommonModule, FormsModule],
  template: `
    <div class="login-shell">
      <section class="login-card">
        <div class="brand-line">
          <p class="eyebrow">Expense Tracker SaaS</p>
          <span class="status-pill">Multiempresa</span>
        </div>
        <h1>Control financiero con foco operativo</h1>
        <p class="muted">Administra empresas, establecimientos y movimientos desde una base Angular mantenible y lista para crecer.</p>

        <label>
          <span>Email</span>
          <input [(ngModel)]="email" type="email" placeholder="admin&#64;sistema.com">
        </label>

        <label>
          <span>Contrasena</span>
          <input [(ngModel)]="password" type="password" placeholder="admin123">
        </label>

        <p *ngIf="error" class="error">{{ error }}</p>

        <button class="btn" type="button" (click)="submit()">Entrar</button>

        <div class="demo">
          <strong>Acceso demo</strong>
          <span>admin&#64;sistema.com</span>
          <span>admin123</span>
        </div>

        <div class="benefits">
          <article>
            <strong>Empresas</strong>
            <span>Gestion centralizada con ingreso explicito por empresa.</span>
          </article>
          <article>
            <strong>Auditoria</strong>
            <span>Seguimiento de accesos y acciones sensibles.</span>
          </article>
        </div>
      </section>
    </div>
  `,
  styles: [`
    .login-shell { min-height: 100vh; display: grid; place-items: center; padding: 24px; position: relative; }
    .login-shell::before, .login-shell::after { content: ""; position: fixed; border-radius: 999px; filter: blur(20px); opacity: .75; pointer-events: none; }
    .login-shell::before { width: 340px; height: 340px; top: 8%; left: 6%; background: radial-gradient(circle, rgba(244,162,97,.34), transparent 70%); }
    .login-shell::after { width: 420px; height: 420px; right: 2%; bottom: 4%; background: radial-gradient(circle, rgba(106,166,217,.28), transparent 70%); }
    .login-card { width: min(540px, 100%); position: relative; overflow: hidden; background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(241,245,249,.90)); border: 1px solid rgba(71, 85, 105, .16); border-radius: 32px; padding: 36px; display: grid; gap: 18px; box-shadow: 0 32px 90px rgba(15, 23, 42, .16); backdrop-filter: blur(18px); }
    .login-card::before { content: ""; position: absolute; inset: 0 auto auto 0; width: 100%; height: 6px; background: linear-gradient(90deg, #f4a261, #2f6ea5); }
    .brand-line { display:flex; justify-content:space-between; gap:12px; align-items:center; flex-wrap:wrap; }
    .eyebrow { margin: 0; text-transform: uppercase; letter-spacing: .22em; font-size: 11px; font-weight: 800; color: #7a6f57; }
    .status-pill { display:inline-flex; align-items:center; padding:8px 12px; border-radius:999px; background:linear-gradient(135deg, rgba(47,110,165,.12), rgba(106,166,217,.24)); color:#24466b; font-size:12px; font-weight:700; }
    h1, p { margin: 0; }
    h1 { font-size: clamp(2rem, 4vw, 3rem); letter-spacing: -.05em; color: #0f172a; max-width: 12ch; }
    .muted { color: var(--muted); line-height: 1.7; max-width: 46ch; }
    label { display: grid; gap: 8px; color: #22314d; font-weight: 700; }
    input { border: 1px solid rgba(71, 85, 105, .18); border-radius: 18px; padding: 15px 16px; background: linear-gradient(180deg, #ffffff, #f8fafc); box-shadow: inset 0 1px 0 rgba(255,255,255,.85); }
    input:focus { outline: 2px solid rgba(106,166,217,.22); border-color: #6aa6d9; }
    .btn { margin-top: 10px; border: 0; border-radius: 999px; padding: 15px 20px; background: linear-gradient(135deg, #0f172a, #2f6ea5); color: #fff; cursor: pointer; font-weight: 700; box-shadow: 0 18px 40px rgba(15, 23, 42, .24); }
    .error { color: var(--danger); font-weight: 600; }
    .demo { display: grid; gap: 4px; background: linear-gradient(135deg, rgba(15,23,42,.04), rgba(244,162,97,.10)); border: 1px solid rgba(71, 85, 105, .14); border-radius: 20px; padding: 16px; color: #51627e; }
    .benefits { display:grid; gap:12px; grid-template-columns:repeat(2, minmax(0,1fr)); }
    .benefits article { padding:16px; border-radius:20px; background:linear-gradient(180deg, rgba(255,255,255,.94), rgba(241,245,249,.86)); border:1px solid rgba(71,85,105,.12); display:grid; gap:6px; }
    .benefits strong { color:#0f172a; }
    .benefits span { color:var(--muted); font-size:14px; line-height:1.5; }
    @media (max-width: 640px) { .benefits { grid-template-columns:1fr; } h1 { max-width:none; } }
  `],
})
export class LoginPageComponent {
  email = 'admin@sistema.com';
  password = 'admin123';
  error = '';

  constructor(private readonly auth: AuthService, private readonly router: Router) {}

  async submit(): Promise<void> {
    this.error = '';
    if (!(await this.auth.login(this.email.trim(), this.password))) {
      this.error = 'Credenciales invalidas';
      return;
    }

    await this.router.navigate(['/']);
  }
}
