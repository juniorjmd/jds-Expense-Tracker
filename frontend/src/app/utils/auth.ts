import { Usuario, RolUsuario } from '../types';

const USUARIOS_KEY = 'gestion-financiera-usuarios';
const USUARIO_ACTUAL_KEY = 'gestion-financiera-usuario-actual';

// Inicializar con un usuario administrador por defecto
export function initializeAuth() {
  const usuarios = getUsuarios();
  if (usuarios.length === 0) {
    const adminPorDefecto: Usuario = {
      id: '1',
      nombre: 'Administrador',
      email: 'admin@sistema.com',
      password: 'admin123', // En producción esto debería estar hasheado
      rol: 'administrador',
      establecimientosAsignados: [], // Admin tiene acceso a todos
      createdAt: new Date().toISOString(),
    };
    saveUsuarios([adminPorDefecto]);
  }
}

export function getUsuarios(): Usuario[] {
  const data = localStorage.getItem(USUARIOS_KEY);
  return data ? JSON.parse(data) : [];
}

export function saveUsuarios(usuarios: Usuario[]) {
  localStorage.setItem(USUARIOS_KEY, JSON.stringify(usuarios));
}

export function getUsuarioActual(): Usuario | null {
  const data = localStorage.getItem(USUARIO_ACTUAL_KEY);
  return data ? JSON.parse(data) : null;
}

export function setUsuarioActual(usuario: Usuario | null) {
  if (usuario) {
    localStorage.setItem(USUARIO_ACTUAL_KEY, JSON.stringify(usuario));
  } else {
    localStorage.removeItem(USUARIO_ACTUAL_KEY);
  }
}

export function login(email: string, password: string): Usuario | null {
  const usuarios = getUsuarios();
  const usuario = usuarios.find(
    (u) => u.email === email && u.password === password
  );
  
  if (usuario) {
    setUsuarioActual(usuario);
    return usuario;
  }
  
  return null;
}

export function logout() {
  setUsuarioActual(null);
}

export function crearUsuario(
  nombre: string,
  email: string,
  password: string,
  rol: RolUsuario,
  establecimientosAsignados: string[]
): Usuario {
  const usuarios = getUsuarios();
  
  // Verificar si el email ya existe
  if (usuarios.some((u) => u.email === email)) {
    throw new Error('El email ya está registrado');
  }
  
  const nuevoUsuario: Usuario = {
    id: Date.now().toString(),
    nombre,
    email,
    password, // En producción esto debería estar hasheado
    rol,
    establecimientosAsignados,
    createdAt: new Date().toISOString(),
  };
  
  saveUsuarios([...usuarios, nuevoUsuario]);
  return nuevoUsuario;
}

export function actualizarUsuario(usuarioId: string, datos: Partial<Usuario>): void {
  const usuarios = getUsuarios();
  const index = usuarios.findIndex((u) => u.id === usuarioId);
  
  if (index !== -1) {
    usuarios[index] = { ...usuarios[index], ...datos };
    saveUsuarios(usuarios);
    
    // Si es el usuario actual, actualizar también
    const usuarioActual = getUsuarioActual();
    if (usuarioActual && usuarioActual.id === usuarioId) {
      setUsuarioActual(usuarios[index]);
    }
  }
}

export function eliminarUsuario(usuarioId: string): void {
  const usuarios = getUsuarios();
  saveUsuarios(usuarios.filter((u) => u.id !== usuarioId));
}

export function tienePermiso(
  usuario: Usuario | null,
  permiso: 'crear' | 'editar' | 'ver_resumen' | 'gestionar_usuarios'
): boolean {
  if (!usuario) return false;
  
  switch (permiso) {
    case 'gestionar_usuarios':
      return usuario.rol === 'administrador';
    case 'crear':
      return usuario.rol === 'administrador' || usuario.rol === 'editor';
    case 'editar':
      return usuario.rol === 'administrador' || usuario.rol === 'editor';
    case 'ver_resumen':
      return usuario.rol === 'administrador';
    default:
      return false;
  }
}

export function tieneAccesoEstablecimiento(
  usuario: Usuario | null,
  establecimientoId: string
): boolean {
  if (!usuario) return false;
  
  // Los administradores tienen acceso a todos los establecimientos
  if (usuario.rol === 'administrador') return true;
  
  // Otros usuarios solo tienen acceso a sus establecimientos asignados
  return usuario.establecimientosAsignados.includes(establecimientoId);
}
