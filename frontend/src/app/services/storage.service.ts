import { Injectable } from '@angular/core';
import { Category, CategoryScope, Company, CompanyOverview, Establishment, ExpenseTemplate, MonthlySummary, Transaction, TransactionType } from '../models';
import { ApiService } from './api.service';

@Injectable({ providedIn: 'root' })
export class StorageService {
  constructor(private readonly api: ApiService) {}

  getCompanies(): Promise<Company[]> {
    return this.api.get<Company[]>('/companies');
  }

  getCompanyOverview(companyId: string, month = new Date().toISOString().slice(0, 7)): Promise<CompanyOverview> {
    return this.api.get<CompanyOverview>(`/companies/${companyId}?month=${month}`);
  }

  saveCompany(payload: {
    name: string;
    description?: string;
    existingAdminUserId?: string;
    adminName?: string;
    adminEmail?: string;
    adminPassword?: string;
  }): Promise<{ company: Company }> {
    return this.api.post<{ company: Company }>('/companies', payload);
  }

  getEstablishments(month = new Date().toISOString().slice(0, 7)): Promise<Establishment[]> {
    return this.api.get<Establishment[]>(`/establishments?month=${month}`);
  }

  saveEstablishment(payload: Omit<Establishment, 'id' | 'createdAt'>): Promise<Establishment> {
    return this.api.post<Establishment>('/establishments', payload);
  }

  async deleteEstablishment(id: string): Promise<void> {
    await this.api.delete<{ deleted: boolean }>(`/establishments/${id}`);
  }

  getTransactionsByEstablishment(establishmentId: string): Promise<Transaction[]> {
    return this.api.get<Transaction[]>(`/establishments/${establishmentId}/transactions`);
  }

  saveTransaction(payload: {
    establishmentId: string;
    type: Extract<TransactionType, 'income' | 'expense'>;
    amount: number;
    category: string;
    description: string;
    date: string;
    fromTemplate?: boolean;
    categoryId?: string | null;
    categoryScope?: CategoryScope;
  }): Promise<Transaction> {
    return this.api.post<Transaction>(`/establishments/${payload.establishmentId}/transactions`, {
      type: payload.type,
      amount: payload.amount,
      category_id: payload.categoryId || undefined,
      category: payload.category,
      category_scope: payload.categoryScope || 'ESTABLECIMIENTO',
      description: payload.description,
      transaction_date: payload.date.slice(0, 10),
      from_template: payload.fromTemplate ?? false,
    });
  }

  updateTransaction(id: string, payload: {
    establishmentId: string;
    type: Extract<TransactionType, 'income' | 'expense'>;
    amount: number;
    category: string;
    description: string;
    date: string;
    categoryId?: string | null;
    categoryScope?: CategoryScope;
  }): Promise<Transaction> {
    return this.api.put<Transaction>(`/transactions/${id}`, {
      establishmentId: payload.establishmentId,
      type: payload.type,
      amount: payload.amount,
      category_id: payload.categoryId || undefined,
      category: payload.category,
      category_scope: payload.categoryScope || 'ESTABLECIMIENTO',
      description: payload.description,
      transaction_date: payload.date.slice(0, 10),
    });
  }

  saveMovement(payload: {
    sourceEstablishmentId: string;
    destinationEstablishmentId: string;
    amount: number;
    category: string;
    description: string;
    date: string;
    categoryId?: string | null;
    categoryScope?: CategoryScope;
  }): Promise<{ movementGroupId: string; source: Transaction; destination: Transaction; transactions: Transaction[] }> {
    return this.api.post<{ movementGroupId: string; source: Transaction; destination: Transaction; transactions: Transaction[] }>(
      `/establishments/${payload.sourceEstablishmentId}/movements`,
      {
        destinationEstablishmentId: payload.destinationEstablishmentId,
        amount: payload.amount,
        category_id: payload.categoryId || undefined,
        category: payload.category,
        category_scope: payload.categoryScope || 'EMPRESA',
        description: payload.description,
        transaction_date: payload.date.slice(0, 10),
      }
    );
  }

  updateMovement(movementGroupId: string, payload: {
    sourceEstablishmentId: string;
    destinationEstablishmentId: string;
    amount: number;
    category: string;
    description: string;
    date: string;
    categoryId?: string | null;
    categoryScope?: CategoryScope;
  }): Promise<{ movementGroupId: string; source: Transaction; destination: Transaction; transactions: Transaction[] }> {
    return this.api.put<{ movementGroupId: string; source: Transaction; destination: Transaction; transactions: Transaction[] }>(
      `/transactions/movements/${movementGroupId}`,
      {
        sourceEstablishmentId: payload.sourceEstablishmentId,
        destinationEstablishmentId: payload.destinationEstablishmentId,
        amount: payload.amount,
        category_id: payload.categoryId || undefined,
        category: payload.category,
        category_scope: payload.categoryScope || 'EMPRESA',
        description: payload.description,
        transaction_date: payload.date.slice(0, 10),
      }
    );
  }

  async deleteTransaction(id: string): Promise<void> {
    await this.api.delete<{ deleted: boolean }>(`/transactions/${id}`);
  }

  getCategories(type?: 'income' | 'expense' | 'movement', establishmentId?: string): Promise<Category[]> {
    const params = new URLSearchParams();
    if (type) {
      params.set('type', type);
    }
    if (establishmentId) {
      params.set('establishmentId', establishmentId);
    }

    const query = params.toString();
    return this.api.get<Category[]>(`/categories${query ? `?${query}` : ''}`);
  }

  saveCategory(payload: {
    name: string;
    type: 'income' | 'expense' | 'movement';
    scope: CategoryScope;
    establishmentId?: string;
    color?: string;
  }): Promise<Category> {
    return this.api.post<Category>('/categories', payload);
  }

  getExpenseTemplatesByEstablishment(establishmentId: string): Promise<ExpenseTemplate[]> {
    return this.api.get<ExpenseTemplate[]>(`/establishments/${establishmentId}/expense-templates`);
  }

  saveExpenseTemplate(payload: Omit<ExpenseTemplate, 'id'>): Promise<ExpenseTemplate> {
    return this.api.post<ExpenseTemplate>(`/establishments/${payload.establishmentId}/expense-templates`, {
      category: payload.category,
      description: payload.description,
      amount: payload.amount,
    });
  }

  async deleteExpenseTemplate(id: string): Promise<void> {
    await this.api.delete<{ deleted: boolean }>(`/expense-templates/${id}`);
  }

  applyExpenseTemplate(template: ExpenseTemplate): Promise<Transaction> {
    return this.api.post<Transaction>(`/expense-templates/${template.id}/apply`, {});
  }

  getSummary(month: string): Promise<MonthlySummary> {
    return this.api.get<MonthlySummary>(`/summary?month=${month}`);
  }
}
