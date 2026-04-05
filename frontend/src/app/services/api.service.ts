import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
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

@Injectable({ providedIn: 'root' })
export class ApiService {
  private readonly baseUrl = `${window.location.origin}/expense-tracker-back/api`;

  constructor(private readonly http: HttpClient) {}

  get<T>(path: string): Promise<T> {
    return this.unwrap(firstValueFrom(this.http.get<ApiResponse<T>>(`${this.baseUrl}${path}`)));
  }

  post<T>(path: string, payload: unknown): Promise<T> {
    return this.unwrap(firstValueFrom(this.http.post<ApiResponse<T>>(`${this.baseUrl}${path}`, payload)));
  }

  put<T>(path: string, payload: unknown): Promise<T> {
    return this.unwrap(firstValueFrom(this.http.put<ApiResponse<T>>(`${this.baseUrl}${path}`, payload)));
  }

  delete<T>(path: string): Promise<T> {
    return this.unwrap(firstValueFrom(this.http.delete<ApiResponse<T>>(`${this.baseUrl}${path}`)));
  }

  private async unwrap<T>(promise: Promise<ApiResponse<T>>): Promise<T> {
    const response = await promise;
    if (!response.ok) {
      throw new Error(response.error?.message || 'La solicitud al backend fallo.');
    }

    return response.data;
  }
}
