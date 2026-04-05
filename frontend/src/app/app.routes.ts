import { Routes } from '@angular/router';
import { authGuard, guestGuard } from './core/auth.guard';
import { DashboardPageComponent } from './pages/dashboard-page.component';
import { CompaniesPageComponent } from './pages/companies-page.component';
import { CompanyDetailPageComponent } from './pages/company-detail-page.component';
import { LoginPageComponent } from './pages/login-page.component';
import { SummaryPageComponent } from './pages/summary-page.component';
import { UsersPageComponent } from './pages/users-page.component';
import { EstablishmentDetailPageComponent } from './pages/establishment-detail-page.component';

export const routes: Routes = [
  {
    path: 'login',
    component: LoginPageComponent,
    canActivate: [guestGuard],
  },
  {
    path: '',
    component: DashboardPageComponent,
    canActivate: [authGuard],
  },
  {
    path: 'empresas',
    component: CompaniesPageComponent,
    canActivate: [authGuard],
  },
  {
    path: 'empresas/:id',
    component: CompanyDetailPageComponent,
    canActivate: [authGuard],
  },
  {
    path: 'establecimiento/:id',
    component: EstablishmentDetailPageComponent,
    canActivate: [authGuard],
  },
  {
    path: 'resumen',
    component: SummaryPageComponent,
    canActivate: [authGuard],
  },
  {
    path: 'usuarios',
    component: UsersPageComponent,
    canActivate: [authGuard],
  },
  {
    path: '**',
    redirectTo: '',
  },
];
