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
        <button
          class="help-button"
          type="button"
          (click)="toggleHelp()"
          [attr.aria-expanded]="showHelp"
          aria-controls="login-help"
          aria-label="Mostrar ayuda sobre la aplicacion"
        >
          ?
        </button>

        <div class="brand-line">
          <p class="eyebrow">Expense Tracker SaaS</p>
          <span class="status-pill">Ingreso</span>
        </div>
        <h1>Ingresa a tu cuenta</h1>
        <p class="muted">Accede con tus credenciales para continuar.</p>

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

        <section *ngIf="showHelp" id="login-help" class="help-panel">
          <div class="help-copy">
            <strong>Sobre la aplicacion</strong>
            <p>Plataforma para administrar empresas, establecimientos y movimientos financieros en un entorno multiempresa.</p>
          </div>
          <div class="help-copy">
            <strong>Funciones principales</strong>
            <p>Incluye seguimiento operativo, control de accesos y paneles para el manejo diario de la operacion.</p>
          </div>
          <div class="help-copy demo-copy">
            <strong>Acceso demo</strong>
            <p>admin&#64;sistema.com<br>admin123</p>
          </div>
        </section>
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
    .help-button { position:absolute; top:22px; right:22px; width:42px; height:42px; border:1px solid rgba(71, 85, 105, .14); border-radius:999px; background:rgba(255,255,255,.9); color:#24466b; font-size:18px; font-weight:800; cursor:pointer; box-shadow:0 10px 25px rgba(15, 23, 42, .10); }
    .help-button:hover, .help-button:focus-visible { outline:none; border-color:#6aa6d9; transform:translateY(-1px); }
    .brand-line { display:flex; justify-content:space-between; gap:12px; align-items:center; flex-wrap:wrap; }
    .eyebrow { margin: 0; text-transform: uppercase; letter-spacing: .22em; font-size: 11px; font-weight: 800; color: #7a6f57; }
    .status-pill { display:inline-flex; align-items:center; padding:8px 12px; border-radius:999px; background:linear-gradient(135deg, rgba(47,110,165,.12), rgba(106,166,217,.24)); color:#24466b; font-size:12px; font-weight:700; }
    h1, p { margin: 0; }
    h1 { font-size: clamp(2rem, 4vw, 3rem); letter-spacing: -.05em; color: #0f172a; max-width: 10ch; }
    .muted { color: var(--muted); line-height: 1.7; max-width: 32ch; }
    label { display: grid; gap: 8px; color: #22314d; font-weight: 700; }
    input { border: 1px solid rgba(71, 85, 105, .18); border-radius: 18px; padding: 15px 16px; background: linear-gradient(180deg, #ffffff, #f8fafc); box-shadow: inset 0 1px 0 rgba(255,255,255,.85); }
    input:focus { outline: 2px solid rgba(106,166,217,.22); border-color: #6aa6d9; }
    .btn { margin-top: 10px; border: 0; border-radius: 999px; padding: 15px 20px; background: linear-gradient(135deg, #0f172a, #2f6ea5); color: #fff; cursor: pointer; font-weight: 700; box-shadow: 0 18px 40px rgba(15, 23, 42, .24); }
    .error { color: var(--danger); font-weight: 600; }
    .help-panel { display:grid; gap:12px; padding:18px; border-radius:24px; background:linear-gradient(180deg, rgba(255,255,255,.96), rgba(241,245,249,.88)); border:1px solid rgba(71,85,105,.12); }
    .help-copy { display:grid; gap:6px; color:#51627e; }
    .help-copy strong { color:#0f172a; }
    .help-copy p { line-height:1.6; }
    .demo-copy p { font-weight:700; color:#24466b; }
    @media (max-width: 640px) { .login-card { padding: 28px 22px; } h1 { max-width:none; } }
  `],
})
export class LoginPageComponent {
  email = 'admin@sistema.com';
  password = 'admin123';
  error = '';
  showHelp = false;

  constructor(private readonly auth: AuthService, private readonly router: Router) {}

  toggleHelp(): void {
    this.showHelp = !this.showHelp;
  }

  async submit(): Promise<void> {
    this.error = '';
    if (!(await this.auth.login(this.email.trim(), this.password))) {
      this.error = 'Credenciales invalidas';
      return;
    }

    await this.router.navigate(['/']);
  }
}
