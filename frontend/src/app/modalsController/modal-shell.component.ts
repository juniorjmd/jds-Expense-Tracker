import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Input, Output } from '@angular/core';

@Component({
  selector: 'app-modal-shell',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="modal-backdrop" [class.elevated]="elevated" (click)="closed.emit()">
      <section
        class="modal-card"
        [class]="cardClass"
        role="dialog"
        aria-modal="true"
        [attr.aria-labelledby]="labelledBy || null"
        [style.--modal-width]="width"
        (click)="$event.stopPropagation()"
      >
        <ng-content></ng-content>
      </section>
    </div>
  `,
  styles: [`
    .modal-backdrop {
      position: fixed;
      inset: 0;
      display: grid;
      place-items: center;
      padding: 24px;
      background: rgba(15, 23, 42, .54);
      backdrop-filter: blur(8px);
      z-index: 30;
      overflow: auto;
    }

    .modal-backdrop.elevated {
      z-index: 40;
    }

    .modal-card {
      width: min(var(--modal-width, 760px), 100%);
      max-height: calc(100vh - 48px);
      overflow: auto;
      display: grid;
      gap: 18px;
      padding: 24px;
      border-radius: 28px;
      background: linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(241, 245, 249, .94));
      border: 1px solid rgba(71, 85, 105, .14);
      box-shadow: 0 32px 90px rgba(15, 23, 42, .24);
    }

    @media (max-width: 768px) {
      .modal-backdrop {
        padding: 16px;
      }

      .modal-card {
        max-height: calc(100vh - 32px);
        padding: 20px;
      }
    }
  `],
})
export class ModalShellComponent {
  @Input() width = '760px';
  @Input() elevated = false;
  @Input() labelledBy = '';
  @Input() cardClass = '';
  @Output() closed = new EventEmitter<void>();
}
