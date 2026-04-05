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
        <p class="eyebrow">Expense Tracker</p>
        <h1>Inicia sesion</h1>
        <p class="muted">Portando la referencia visual de Figma a una base Angular mantenible.</p>

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
      </section>
    </div>
  `,
  styles: [`
    .login-shell { min-height: 100vh; display: grid; place-items: center; padding: 24px; position: relative; }
    .login-shell::before, .login-shell::after { content: ""; position: fixed; border-radius: 999px; filter: blur(18px); opacity: .75; pointer-events: none; }
    .login-shell::before { width: 320px; height: 320px; top: 8%; left: 8%; background: radial-gradient(circle, rgba(240,163,76,.35), transparent 70%); }
    .login-shell::after { width: 380px; height: 380px; right: 4%; bottom: 6%; background: radial-gradient(circle, rgba(66,124,202,.28), transparent 70%); }
    .login-card { width: min(460px, 100%); position: relative; overflow: hidden; background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(255,255,255,.86)); border: 1px solid rgba(103, 129, 177, .18); border-radius: 32px; padding: 36px; display: grid; gap: 16px; box-shadow: 0 32px 90px rgba(23, 35, 68, .16); backdrop-filter: blur(18px); }
    .login-card::before { content: ""; position: absolute; inset: 0 auto auto 0; width: 100%; height: 6px; background: linear-gradient(90deg, #d98d43, #3f7cbf); }
    .eyebrow { margin: 0; text-transform: uppercase; letter-spacing: .22em; font-size: 11px; font-weight: 800; color: #7a6f57; }
    h1, p { margin: 0; }
    h1 { font-size: clamp(2rem, 4vw, 2.8rem); letter-spacing: -.04em; color: #14233f; }
    .muted { color: #61728f; line-height: 1.7; max-width: 34ch; }
    label { display: grid; gap: 8px; color: #2b3954; font-weight: 700; }
    input { border: 1px solid rgba(100, 126, 176, .24); border-radius: 18px; padding: 15px 16px; background: linear-gradient(180deg, #ffffff, #f7faff); box-shadow: inset 0 1px 0 rgba(255,255,255,.85); }
    input:focus { outline: 2px solid rgba(63,124,191,.22); border-color: #7ba4d8; }
    .btn { margin-top: 10px; border: 0; border-radius: 999px; padding: 15px 20px; background: linear-gradient(135deg, #173a63, #3f7cbf); color: #fff; cursor: pointer; font-weight: 700; box-shadow: 0 18px 40px rgba(31, 77, 140, .28); }
    .error { color: #d24f45; font-weight: 600; }
    .demo { display: grid; gap: 4px; background: linear-gradient(135deg, rgba(23,58,99,.06), rgba(217,141,67,.08)); border: 1px solid rgba(93, 113, 153, .14); border-radius: 20px; padding: 16px; color: #51627e; }
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
