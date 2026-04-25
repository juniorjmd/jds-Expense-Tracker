import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Output } from '@angular/core';
import { ModalShellComponent } from './modal-shell.component';

@Component({
  selector: 'app-login-help-modal',
  standalone: true,
  imports: [CommonModule, ModalShellComponent],
  template: `
    <app-modal-shell width="460px" labelledBy="login-help-title" (closed)="closed.emit()">
      <div id="login-help-dialog" class="help-dialog">
        <button class="help-close" type="button" (click)="closed.emit()" aria-label="Cerrar ayuda">×</button>
        <div class="help-copy">
          <strong id="login-help-title">Sobre la aplicacion</strong>
          <p>Plataforma para administrar empresas, establecimientos y movimientos financieros en un entorno multiempresa.</p>
        </div>
        <div class="help-copy">
          <strong>Funciones principales</strong>
          <p>Incluye seguimiento operativo, control de accesos y paneles para el manejo diario de la operacion.</p>
        </div>
      </div>
    </app-modal-shell>
  `,
  styles: [`
    .help-dialog { position:relative; display:grid; gap:14px; }
    .help-close { position:absolute; top:14px; right:14px; width:38px; height:38px; border:0; border-radius:999px; background:rgba(15,23,42,.08); color:#24466b; font-size:22px; cursor:pointer; }
    .help-copy { display:grid; gap:6px; color:#51627e; }
    .help-copy strong { color:#0f172a; }
    .help-copy p { line-height:1.6; margin:0; }
  `],
})
export class LoginHelpModalComponent {
  @Output() closed = new EventEmitter<void>();
}
