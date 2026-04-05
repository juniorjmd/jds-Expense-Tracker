export interface Company {
  id: string;
  name: string;
  description?: string;
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

export interface CompanyOverview {
  company: Company;
  summary: MonthlySummary;
  establishments: Establishment[];
  users: User[];
  accessLogs: CompanyAccessLog[];
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

export interface Transaction {
  id: string;
  companyId?: string;
  establishmentId: string;
  type: 'income' | 'expense';
  amount: number;
  category: string;
  description: string;
  date: string;
  fromTemplate?: boolean;
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
