export interface Company {
  id: string;
  name: string;
  description?: string;
  planCode?: string;
  planName?: string;
  subscriptionStatus?: string;
  currencyCode?: string;
  timezone?: string;
  dateFormat?: string;
  brandingName?: string;
  createdAt: string;
  establishmentsCount?: number;
  usersCount?: number;
}

export interface CompanyAccessLog {
  id: string;
  companyId: string;
  actorUserId: string;
  actorName: string;
  actorEmail: string;
  action: string;
  note?: string;
  createdAt: string;
}

export interface ActivityLog {
  id: string;
  companyId?: string | null;
  establishmentId?: string | null;
  actorUserId: string;
  actorName: string;
  actorEmail: string;
  entityType: string;
  entityId: string;
  action: string;
  note?: string;
  createdAt: string;
}

export interface CompanyOverview {
  company: Company;
  settings?: {
    currencyCode: string;
    timezone: string;
    dateFormat: string;
    brandingName?: string;
  };
  subscription?: {
    status: string;
    planCode: string;
    planName: string;
  };
  summary: MonthlySummary;
  establishments: Establishment[];
  users: User[];
  accessLogs: CompanyAccessLog[];
  activityLogs: ActivityLog[];
}

export interface Establishment {
  id: string;
  companyId?: string;
  companyName?: string;
  name: string;
  description?: string;
  createdAt: string;
  transactionCount?: number;
  income?: number;
  expense?: number;
  balance?: number;
}

export type TransactionType = 'income' | 'expense' | 'SALIDA_POR_MOVIMIENTO' | 'INGRESO_POR_MOVIMIENTO';

export type CategoryScope = 'EMPRESA' | 'ESTABLECIMIENTO';

export interface Category {
  id: string;
  companyId: string;
  establishmentId?: string | null;
  name: string;
  type: 'income' | 'expense' | 'movement';
  scope: CategoryScope;
  color?: string;
  createdAt: string;
  updatedAt?: string;
}

export interface Transaction {
  id: string;
  companyId?: string;
  establishmentId: string;
  categoryId?: string | null;
  relatedEstablishmentId?: string | null;
  movementGroupId?: string;
  type: TransactionType;
  amount: number;
  category: string;
  description: string;
  date: string;
  fromTemplate?: boolean;
  createdAt?: string;
  updatedAt?: string;
}

export interface ExpenseTemplate {
  id: string;
  companyId?: string;
  establishmentId: string;
  category: string;
  description: string;
  amount: number;
  createdAt?: string;
}

export type UserRole = 'superusuario' | 'administrador' | 'editor' | 'visualizador';

export interface User {
  id: string;
  companyId?: string | null;
  companyName?: string | null;
  administeredCompanies?: Array<Pick<Company, 'id' | 'name' | 'description' | 'planName' | 'subscriptionStatus'>>;
  name: string;
  email: string;
  password?: string;
  role: UserRole;
  assignedEstablishments: string[];
  createdAt: string;
}

export interface MonthlySummary {
  month: string;
  income: number;
  expense: number;
  balance: number;
  label?: string;
  transactions?: Transaction[];
  months?: string[];
  breakdown?: Array<{ id: string; name: string; income: number; expense: number; balance: number }>;
}
