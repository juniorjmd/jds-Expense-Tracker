import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { ApiRequestError } from '../services/api.service';
import { AuthService } from '../services/auth.service';

@Component({
  selector: 'app-superuser-password-page',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  template: `
    <div class="shell" *ngIf="auth.getCurrentUser()?.role === 'superusuario'; else blocked">
      <header class="hero">
        <div class="hero-copy">
          <p class="eyebrow">Seguridad del acceso maestro</p>
          <h1>Cambia la clave del superusuario</h1>
          <p class="muted">
            Actualiza la contrasena de la cuenta principal con una validacion directa de la clave actual
            antes de guardar el cambio.
          </p>
        </div>

        <div class="hero-actions">
          <a routerLink="/" class="btn ghost">Volver al panel</a>
          <span class="badge">{{ auth.getCurrentUser()?.email }}</span>
        </div>
      </header>

      <section class="panel form-panel">
        <div class="panel-head">
          <h2>Formulario seguro</h2>
          <p class="muted">
            Usa una contrasena nueva de al menos 8 caracteres y evita repetir la misma clave actual.
          </p>
        </div>

        <div class="form-grid">
          <label class="full">
            <span>Contrasena actual</span>
            <input [(ngModel)]="currentPassword" type="password" autocomplete="current-password" placeholder="Ingresa la contrasena actual">
          </label>

          <label>
            <span>Nueva contrasena</span>
            <input [(ngModel)]="newPassword" type="password" autocomplete="new-password" placeholder="Minimo 8 caracteres">
          </label>

          <label>
            <span>Confirmar nueva contrasena</span>
            <input [(ngModel)]="confirmPassword" type="password" autocomplete="new-password" placeholder="Repite la nueva contrasena">
          </label>
        </div>

        <p *ngIf="errorMessage" class="feedback error">{{ errorMessage }}</p>
        <p *ngIf="successMessage" class="feedback success">{{ successMessage }}</p>

        <div class="actions">
          <button class="btn" type="button" (click)="save()" [disabled]="saving">
            {{ saving ? 'Guardando...' : 'Actualizar contrasena' }}
          </button>
        </div>
      </section>

      <section class="panel tips">
        <div class="panel-head">
          <h2>Recomendaciones</h2>
        </div>

        <div class="tip-grid">
          <article class="tip">
            <strong>Evita claves recicladas</strong>
            <p>Usa una combinacion distinta a cualquier clave que ya hayas usado para administracion o hosting.</p>
          </article>

          <article class="tip">
            <strong>Guarda el acceso en un vault</strong>
            <p>Al ser la cuenta raiz del sistema, conviene registrarla en un gestor seguro y no en notas sueltas.</p>
          </article>

          <article class="tip">
            <strong>Prueba el ingreso despues del cambio</strong>
            <p>Cuando termines, cierra sesion y valida de inmediato que el nuevo acceso funcione correctamente.</p>
          </article>
        </div>
      </section>
    </div>

    <ng-template #blocked>
      <div class="shell">
        <section class="panel">
          <h1>Sin acceso</h1>
          <p class="muted">Esta pagina solo esta disponible para el superusuario.</p>
          <div class="actions">
            <a routerLink="/" class="btn">Volver</a>
          </div>
        </section>
      </div>
    </ng-template>
  `,
  styles: [`
    .shell { padding: 32px; display: grid; gap: 24px; }
    .hero, .panel, .tip { background: var(--surface); border: 1px solid var(--surface-border); border-radius: 28px; box-shadow: var(--shadow-card); backdrop-filter: blur(14px); }
    .hero { padding: 32px; display: flex; justify-content: space-between; gap: 24px; align-items: flex-start; color: #fff; background: linear-gradient(135deg, rgba(15,23,42,.98), rgba(129,56,24,.94) 42%, rgba(244,162,97,.88)); position: relative; overflow: hidden; }
    .hero::after { content: ""; position: absolute; width: 280px; height: 280px; right: -80px; top: -90px; border-radius: 999px; background: radial-gradient(circle, rgba(255,255,255,.22), transparent 68%); pointer-events: none; }
    .hero-copy { display: grid; gap: 10px; max-width: 56ch; }
    .hero-actions { display: grid; gap: 12px; justify-items: end; }
    .eyebrow { margin: 0; text-transform: uppercase; letter-spacing: .22em; font-size: 11px; font-weight: 800; color: rgba(255,255,255,.76); }
    h1, h2, p, strong { margin: 0; }
    h1 { font-size: clamp(2rem, 4vw, 3rem); letter-spacing: -.05em; }
    .muted { color: var(--muted); }
    .hero .muted { color: rgba(255,255,255,.82); }
    .badge { display: inline-flex; align-items: center; padding: 10px 14px; border-radius: 999px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.2); color: #fff; font-weight: 700; }
    .panel { padding: 24px; display: grid; gap: 18px; background: var(--surface-strong); }
    .panel-head { display: grid; gap: 6px; }
    .form-grid { display: grid; gap: 14px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .full { grid-column: 1 / -1; }
    label { display: grid; gap: 8px; color: #22314d; font-weight: 700; }
    input { width: 100%; border: 1px solid rgba(71, 85, 105, .18); border-radius: 18px; padding: 14px 16px; background: linear-gradient(180deg, #ffffff, #f8fafc); box-shadow: inset 0 1px 0 rgba(255,255,255,.85); }
    input:focus { outline: 2px solid rgba(106,166,217,.22); border-color: #6aa6d9; }
    .actions { display: flex; gap: 12px; flex-wrap: wrap; }
    .btn { border: 0; border-radius: 999px; padding: 12px 18px; background: linear-gradient(135deg, #0f172a, #2f6ea5); color: #fff; text-decoration: none; cursor: pointer; font-weight: 700; box-shadow: 0 16px 30px rgba(15, 23, 42, .20); }
    .btn:disabled { opacity: .7; cursor: wait; }
    .btn.ghost { background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.22); box-shadow: none; }
    .feedback { margin: 0; font-weight: 700; }
    .feedback.error { color: var(--danger); }
    .feedback.success { color: var(--success); }
    .tip-grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
    .tip { padding: 18px; display: grid; gap: 8px; background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(241,245,249,.90)); }
    .tip p { color: #64748b; line-height: 1.65; }
    @media (max-width: 768px) { .shell { padding: 18px; } .hero { flex-direction: column; } .hero-actions { justify-items: start; } .form-grid { grid-template-columns: 1fr; } }
  `],
})
export class SuperuserPasswordPageComponent {
  currentPassword = '';
  newPassword = '';
  confirmPassword = '';
  errorMessage = '';
  successMessage = '';
  saving = false;

  constructor(public readonly auth: AuthService, private readonly router: Router) {}

  async save(): Promise<void> {
    this.errorMessage = '';
    this.successMessage = '';

    if (this.auth.getCurrentUser()?.role !== 'superusuario') {
      await this.router.navigate(['/']);
      return;
    }

    if (!this.currentPassword || !this.newPassword || !this.confirmPassword) {
      this.errorMessage = 'Completa la contrasena actual, la nueva y la confirmacion.';
      return;
    }

    if (this.newPassword.length < 8) {
      this.errorMessage = 'La nueva contrasena debe tener al menos 8 caracteres.';
      return;
    }

    if (this.newPassword !== this.confirmPassword) {
      this.errorMessage = 'La nueva contrasena y su confirmacion no coinciden.';
      return;
    }

    this.saving = true;

    try {
      await this.auth.changeCurrentPassword({
        currentPassword: this.currentPassword,
        newPassword: this.newPassword,
        confirmPassword: this.confirmPassword,
      });

      this.currentPassword = '';
      this.newPassword = '';
      this.confirmPassword = '';
      this.successMessage = 'La contrasena del superusuario se actualizo correctamente.';
    } catch (error) {
      this.errorMessage = error instanceof ApiRequestError ? error.message : 'No fue posible actualizar la contrasena.';
    } finally {
      this.saving = false;
    }
  }
}
