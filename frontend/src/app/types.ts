export interface Establecimiento {
  id: string;
  nombre: string;
  descripcion?: string;
  createdAt: string;
}

export interface Transaccion {
  id: string;
  establecimientoId: string;
  tipo: 'ingreso' | 'gasto';
  monto: number;
  categoria: string;
  descripcion: string;
  fecha: string;
  esPredeterminado?: boolean;
}

export interface GastoPredeterminado {
  id: string;
  establecimientoId: string;
  categoria: string;
  descripcion: string;
  monto: number;
}

export interface ResumenMensual {
  mes: string;
  totalIngresos: number;
  totalGastos: number;
  balance: number;
  transacciones: Transaccion[];
}

export type RolUsuario = 'administrador' | 'editor' | 'visualizador';

export interface Usuario {
  id: string;
  nombre: string;
  email: string;
  password: string; // En producción esto debería estar hasheado
  rol: RolUsuario;
  establecimientosAsignados: string[]; // IDs de establecimientos a los que tiene acceso
  createdAt: string;
}

export interface AuthContext {
  usuario: Usuario | null;
  login: (email: string, password: string) => boolean;
  logout: () => void;
  isAuthenticated: boolean;
  tienePermiso: (permiso: 'crear' | 'editar' | 'ver_resumen' | 'gestionar_usuarios') => boolean;
  tieneAccesoEstablecimiento: (establecimientoId: string) => boolean;
}