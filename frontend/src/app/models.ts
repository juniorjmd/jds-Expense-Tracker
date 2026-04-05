export interface Establishment {
  id: string;
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
  establishmentId: string;
  category: string;
  description: string;
  amount: number;
  createdAt?: string;
}

export type UserRole = 'administrador' | 'editor' | 'visualizador';

export interface User {
  id: string;
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
