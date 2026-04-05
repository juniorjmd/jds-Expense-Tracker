import { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { Usuario, AuthContext as AuthContextType } from '../types';
import * as authUtils from '../utils/auth';

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [usuario, setUsuario] = useState<Usuario | null>(null);

  useEffect(() => {
    // Inicializar el sistema de autenticación
    authUtils.initializeAuth();
    
    // Cargar el usuario actual si existe
    const usuarioActual = authUtils.getUsuarioActual();
    setUsuario(usuarioActual);
  }, []);

  const login = (email: string, password: string): boolean => {
    const usuarioLogueado = authUtils.login(email, password);
    if (usuarioLogueado) {
      setUsuario(usuarioLogueado);
      return true;
    }
    return false;
  };

  const logout = () => {
    authUtils.logout();
    setUsuario(null);
  };

  const tienePermiso = (permiso: 'crear' | 'editar' | 'ver_resumen' | 'gestionar_usuarios'): boolean => {
    return authUtils.tienePermiso(usuario, permiso);
  };

  const tieneAccesoEstablecimiento = (establecimientoId: string): boolean => {
    return authUtils.tieneAccesoEstablecimiento(usuario, establecimientoId);
  };

  const value: AuthContextType = {
    usuario,
    login,
    logout,
    isAuthenticated: !!usuario,
    tienePermiso,
    tieneAccesoEstablecimiento,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth debe ser usado dentro de un AuthProvider');
  }
  return context;
}
