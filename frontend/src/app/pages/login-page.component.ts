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
    .login-shell { min-height: 100vh; display: grid; place-items: center; padding: 24px; background: radial-gradient(circle at top, #dfeaff, #eff3fb 46%, #f7f9fc); }
    .login-card { width: min(440px, 100%); background: rgba(255,255,255,.92); border-radius: 28px; padding: 34px; display: grid; gap: 16px; box-shadow: 0 25px 80px rgba(23, 35, 68, .14); }
    .eyebrow { margin: 0; text-transform: uppercase; letter-spacing: .18em; font-size: 12px; font-weight: 700; color: #60708f; }
    h1, p { margin: 0; }
    .muted { color: #6c7891; line-height: 1.6; }
    label { display: grid; gap: 8px; color: #2b3954; font-weight: 600; }
    input { border: 1px solid #d9e1ef; border-radius: 16px; padding: 14px 16px; background: #f9fbff; font: inherit; }
    .btn { margin-top: 6px; border: 0; border-radius: 999px; padding: 14px 20px; background: #14213d; color: #fff; cursor: pointer; font: inherit; }
    .error { color: #d24f45; font-weight: 600; }
    .demo { display: grid; gap: 4px; background: #f4f7ff; border-radius: 18px; padding: 16px; color: #51627e; }
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
