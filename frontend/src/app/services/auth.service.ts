import { Injectable } from '@angular/core';
import { User, UserRole } from '../models';
import { ApiService } from './api.service';

const CURRENT_USER_KEY = 'expense-tracker.current-user';

@Injectable({ providedIn: 'root' })
export class AuthService {
  constructor(private readonly api: ApiService) {}

  isAuthenticated(): boolean {
    return this.getCurrentUser() !== null;
  }

  getCurrentUser(): User | null {
    const raw = localStorage.getItem(CURRENT_USER_KEY);
    if (!raw) {
      return null;
    }

    try {
      return JSON.parse(raw) as User;
    } catch {
      return null;
    }
  }

  async login(email: string, password: string): Promise<boolean> {
    try {
      const user = await this.api.post<User>('/auth/login', { email, password });
      localStorage.setItem(CURRENT_USER_KEY, JSON.stringify(user));
      return true;
    } catch {
      return false;
    }
  }

  logout(): void {
    localStorage.removeItem(CURRENT_USER_KEY);
  }

  getUsers(): Promise<User[]> {
    return this.api.get<User[]>('/users');
  }

  async saveUser(payload: {
    id?: string;
    name: string;
    email: string;
    password?: string;
    role: UserRole;
    assignedEstablishments: string[];
  }): Promise<User> {
    const body = {
      ...payload,
      assignedEstablishments: payload.role === 'administrador' ? [] : payload.assignedEstablishments,
    };

    const user = await (payload.id
      ? this.api.put<User>(`/users/${payload.id}`, body)
      : this.api.post<User>('/users', body));

    const current = this.getCurrentUser();
    if (current?.id === user.id) {
      localStorage.setItem(CURRENT_USER_KEY, JSON.stringify(user));
    }

    return user;
  }

  async deleteUser(id: string): Promise<void> {
    await this.api.delete<{ deleted: boolean }>(`/users/${id}`);
  }

  can(permission: 'create' | 'edit' | 'view-summary' | 'manage-users'): boolean {
    const user = this.getCurrentUser();
    if (!user) {
      return false;
    }

    if (user.role === 'administrador') {
      return true;
    }

    switch (permission) {
      case 'manage-users':
      case 'view-summary':
        return false;
      case 'create':
      case 'edit':
        return user.role === 'editor';
    }
  }

  canAccessEstablishment(establishmentId: string): boolean {
    const user = this.getCurrentUser();
    if (!user) {
      return false;
    }

    return user.role === 'administrador' || user.assignedEstablishments.includes(establishmentId);
  }
}
