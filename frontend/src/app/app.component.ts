import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet],
  template: `
    <div class="app-shell">
      <router-outlet />
      <footer class="brand-footer">
        <span>Powered by</span>
        <a href="https://sofdla.net" target="_blank" rel="noreferrer">sofdla.net</a>
      </footer>
    </div>
  `,
  styles: [`
    .app-shell { min-height: 100vh; display: grid; grid-template-rows: 1fr auto; }
    .brand-footer {
      display: flex;
      justify-content: center;
      gap: 6px;
      align-items: center;
      padding: 10px 18px 18px;
      color: var(--muted);
      font-size: 13px;
      letter-spacing: .01em;
    }
    .brand-footer a {
      color: var(--brand-2);
      text-decoration: none;
      font-weight: 700;
    }
    .brand-footer a:hover { color: var(--brand); }
  `],
})
export class AppComponent {}
