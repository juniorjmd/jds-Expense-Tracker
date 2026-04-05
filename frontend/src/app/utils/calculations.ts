import { Transaccion, ResumenMensual } from '../types';
import { format, startOfMonth, endOfMonth, parseISO, isSameMonth } from 'date-fns';
import { es } from 'date-fns/locale';

export const calcularResumenMensual = (transacciones: Transaccion[], fecha: Date): ResumenMensual => {
  const transaccionesDelMes = transacciones.filter(t => 
    isSameMonth(parseISO(t.fecha), fecha)
  );

  const totalIngresos = transaccionesDelMes
    .filter(t => t.tipo === 'ingreso')
    .reduce((sum, t) => sum + t.monto, 0);

  const totalGastos = transaccionesDelMes
    .filter(t => t.tipo === 'gasto')
    .reduce((sum, t) => sum + t.monto, 0);

  return {
    mes: format(fecha, 'MMMM yyyy', { locale: es }),
    totalIngresos,
    totalGastos,
    balance: totalIngresos - totalGastos,
    transacciones: transaccionesDelMes.sort((a, b) => 
      new Date(b.fecha).getTime() - new Date(a.fecha).getTime()
    ),
  };
};

export const obtenerMesesDisponibles = (transacciones: Transaccion[]): Date[] => {
  const meses = new Set<string>();
  
  transacciones.forEach(t => {
    const fecha = parseISO(t.fecha);
    const mesKey = format(startOfMonth(fecha), 'yyyy-MM');
    meses.add(mesKey);
  });

  // También agregar el mes actual
  const mesActual = format(startOfMonth(new Date()), 'yyyy-MM');
  meses.add(mesActual);

  return Array.from(meses)
    .sort()
    .reverse()
    .map(mes => parseISO(`${mes}-01`));
};

export const formatearMoneda = (monto: number): string => {
  return new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'USD',
  }).format(monto);
};
