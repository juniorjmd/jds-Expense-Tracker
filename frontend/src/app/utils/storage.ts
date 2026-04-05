import { Establecimiento, Transaccion, GastoPredeterminado } from '../types';

const STORAGE_KEYS = {
  ESTABLECIMIENTOS: 'establecimientos',
  TRANSACCIONES: 'transacciones',
  GASTOS_PREDETERMINADOS: 'gastosPredeterminados',
};

// Establecimientos
export const getEstablecimientos = (): Establecimiento[] => {
  const data = localStorage.getItem(STORAGE_KEYS.ESTABLECIMIENTOS);
  return data ? JSON.parse(data) : [];
};

export const saveEstablecimiento = (establecimiento: Establecimiento): void => {
  const establecimientos = getEstablecimientos();
  establecimientos.push(establecimiento);
  localStorage.setItem(STORAGE_KEYS.ESTABLECIMIENTOS, JSON.stringify(establecimientos));
};

export const updateEstablecimiento = (id: string, updated: Partial<Establecimiento>): void => {
  const establecimientos = getEstablecimientos();
  const index = establecimientos.findIndex(e => e.id === id);
  if (index !== -1) {
    establecimientos[index] = { ...establecimientos[index], ...updated };
    localStorage.setItem(STORAGE_KEYS.ESTABLECIMIENTOS, JSON.stringify(establecimientos));
  }
};

export const deleteEstablecimiento = (id: string): void => {
  const establecimientos = getEstablecimientos().filter(e => e.id !== id);
  localStorage.setItem(STORAGE_KEYS.ESTABLECIMIENTOS, JSON.stringify(establecimientos));
  
  // También eliminar las transacciones relacionadas
  const transacciones = getTransacciones().filter(t => t.establecimientoId !== id);
  localStorage.setItem(STORAGE_KEYS.TRANSACCIONES, JSON.stringify(transacciones));
  
  // Y los gastos predeterminados
  const gastos = getGastosPredeterminados().filter(g => g.establecimientoId !== id);
  localStorage.setItem(STORAGE_KEYS.GASTOS_PREDETERMINADOS, JSON.stringify(gastos));
};

// Transacciones
export const getTransacciones = (): Transaccion[] => {
  const data = localStorage.getItem(STORAGE_KEYS.TRANSACCIONES);
  return data ? JSON.parse(data) : [];
};

export const getTransaccionesByEstablecimiento = (establecimientoId: string): Transaccion[] => {
  return getTransacciones().filter(t => t.establecimientoId === establecimientoId);
};

export const saveTransaccion = (transaccion: Transaccion): void => {
  const transacciones = getTransacciones();
  transacciones.push(transaccion);
  localStorage.setItem(STORAGE_KEYS.TRANSACCIONES, JSON.stringify(transacciones));
};

export const deleteTransaccion = (id: string): void => {
  const transacciones = getTransacciones().filter(t => t.id !== id);
  localStorage.setItem(STORAGE_KEYS.TRANSACCIONES, JSON.stringify(transacciones));
};

// Gastos Predeterminados
export const getGastosPredeterminados = (): GastoPredeterminado[] => {
  const data = localStorage.getItem(STORAGE_KEYS.GASTOS_PREDETERMINADOS);
  return data ? JSON.parse(data) : [];
};

export const getGastosPredeterminadosByEstablecimiento = (establecimientoId: string): GastoPredeterminado[] => {
  return getGastosPredeterminados().filter(g => g.establecimientoId === establecimientoId);
};

export const saveGastoPredeterminado = (gasto: GastoPredeterminado): void => {
  const gastos = getGastosPredeterminados();
  gastos.push(gasto);
  localStorage.setItem(STORAGE_KEYS.GASTOS_PREDETERMINADOS, JSON.stringify(gastos));
};

export const deleteGastoPredeterminado = (id: string): void => {
  const gastos = getGastosPredeterminados().filter(g => g.id !== id);
  localStorage.setItem(STORAGE_KEYS.GASTOS_PREDETERMINADOS, JSON.stringify(gastos));
};

export const aplicarGastoPredeterminado = (gastoPredeterminado: GastoPredeterminado): void => {
  const transaccion: Transaccion = {
    id: crypto.randomUUID(),
    establecimientoId: gastoPredeterminado.establecimientoId,
    tipo: 'gasto',
    monto: gastoPredeterminado.monto,
    categoria: gastoPredeterminado.categoria,
    descripcion: gastoPredeterminado.descripcion,
    fecha: new Date().toISOString(),
    esPredeterminado: true,
  };
  saveTransaccion(transaccion);
};
