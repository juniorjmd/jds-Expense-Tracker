import { Injectable } from '@angular/core';
import { MonthlySummary, Transaction } from '../models';

@Injectable({ providedIn: 'root' })
export class SummaryService {
  buildMonthlySummary(transactions: Transaction[], baseDate: Date): MonthlySummary {
    const monthKey = `${baseDate.getFullYear()}-${String(baseDate.getMonth() + 1).padStart(2, '0')}`;
    const monthlyTransactions = transactions
      .filter((item) => item.date.slice(0, 7) === monthKey)
      .sort((left, right) => new Date(right.date).getTime() - new Date(left.date).getTime());

    const income = monthlyTransactions
      .filter((item) => item.type === 'income')
      .reduce((total, item) => total + item.amount, 0);

    const expense = monthlyTransactions
      .filter((item) => item.type === 'expense')
      .reduce((total, item) => total + item.amount, 0);

    return {
      month: monthKey,
      label: baseDate.toLocaleDateString('es-CO', { month: 'long', year: 'numeric' }),
      income,
      expense,
      balance: income - expense,
      transactions: monthlyTransactions,
    };
  }

  formatCurrency(value: number): string {
    return new Intl.NumberFormat('es-CO', {
      style: 'currency',
      currency: 'COP',
      maximumFractionDigits: 0,
    }).format(value);
  }

  availableMonths(transactions: Transaction[]): string[] {
    const months = new Set<string>();
    transactions.forEach((item) => months.add(item.date.slice(0, 7)));
    months.add(new Date().toISOString().slice(0, 7));

    return Array.from(months).sort().reverse();
  }
}
