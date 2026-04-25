import { Injectable } from '@angular/core';
import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
import { firstValueFrom } from 'rxjs';

interface ApiErrorPayload {
  code: string;
  message: string;
}

interface ApiResponse<T> {
  ok: boolean;
  data: T;
  error: ApiErrorPayload | null;
}

export class ApiRequestError extends Error {
  constructor(
    message: string,
    public readonly code: string,
    public readonly status: number,
    public readonly details?: unknown
  ) {
    super(message);
  }
}

declare global {
  interface Window {
    __APP_CONFIG__?: {
      apiBaseUrl?: string;
    };
  }
}

@Injectable({ providedIn: 'root' })
export class ApiService {
  private readonly baseUrl = this.resolveBaseUrl();

  constructor(private readonly http: HttpClient) {}

  get<T>(path: string): Promise<T> {
    return this.unwrap(firstValueFrom(this.http.get<ApiResponse<T>>(`${this.baseUrl}${path}`, { headers: this.headers() })));
  }

  post<T>(path: string, payload: unknown): Promise<T> {
    return this.unwrap(firstValueFrom(this.http.post<ApiResponse<T>>(`${this.baseUrl}${path}`, payload, { headers: this.headers() })));
  }

  put<T>(path: string, payload: unknown): Promise<T> {
    return this.unwrap(firstValueFrom(this.http.put<ApiResponse<T>>(`${this.baseUrl}${path}`, payload, { headers: this.headers() })));
  }

  delete<T>(path: string): Promise<T> {
    return this.unwrap(firstValueFrom(this.http.delete<ApiResponse<T>>(`${this.baseUrl}${path}`, { headers: this.headers() })));
  }

  private headers(): HttpHeaders {
    const raw = localStorage.getItem('expense-tracker.current-user');
    if (!raw) {
      return new HttpHeaders();
    }

    try {
      const user = JSON.parse(raw) as { id?: string; companyId?: string | null };
      let headers = new HttpHeaders();
      if (user.id) {
        headers = headers.set('X-User-Id', user.id);
      }
      if (user.companyId) {
        headers = headers.set('X-Company-Id', user.companyId);
      }
      return headers;
    } catch {
      return new HttpHeaders();
    }
  }

  private resolveBaseUrl(): string {
    const runtimeConfigured = window.__APP_CONFIG__?.apiBaseUrl?.trim();
    if (runtimeConfigured) {
      return runtimeConfigured.replace(/\/+$/, '');
    }

    if (window.location.hostname === 'expense-tracker.sofdla.net') {
      return 'https://expense-tracker-php.sofdla.net/api';
    }

    return `${window.location.origin}/expense-tracker-back/api`;
  }

  private async unwrap<T>(promise: Promise<ApiResponse<T>>): Promise<T> {
    try {
      const response = await promise;
      if (!response.ok) {
        throw new ApiRequestError(
          response.error?.message || 'La solicitud al backend fallo.',
          response.error?.code || 'API_ERROR',
          400,
          response.error
        );
      }

      return response.data;
    } catch (error) {
      if (error instanceof ApiRequestError) {
        throw error;
      }

      if (error instanceof HttpErrorResponse) {
        const payload = error.error as ApiResponse<T> | null;
        throw new ApiRequestError(
          payload?.error?.message || error.message || 'La solicitud al backend fallo.',
          payload?.error?.code || 'HTTP_ERROR',
          error.status,
          payload?.error ?? error.error
        );
      }

      throw new ApiRequestError('No fue posible completar la solicitud.', 'UNEXPECTED_ERROR', 0, error);
    }
  }
}
