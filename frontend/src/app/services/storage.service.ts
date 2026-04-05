import { Injectable } from '@angular/core';
import { Company, CompanyOverview, Establishment, ExpenseTemplate, MonthlySummary, Transaction } from '../models';
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
    adminName: string;
    adminEmail: string;
    adminPassword: string;
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

  saveTransaction(payload: Omit<Transaction, 'id'>): Promise<Transaction> {
    return this.api.post<Transaction>(`/establishments/${payload.establishmentId}/transactions`, {
      type: payload.type,
      amount: payload.amount,
      category: payload.category,
      description: payload.description,
      transaction_date: payload.date.slice(0, 10),
      from_template: payload.fromTemplate ?? false,
    });
  }

  async deleteTransaction(id: string): Promise<void> {
    await this.api.delete<{ deleted: boolean }>(`/transactions/${id}`);
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
