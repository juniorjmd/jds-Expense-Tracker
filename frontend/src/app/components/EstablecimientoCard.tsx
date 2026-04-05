import { Link } from 'react-router';
import { Establecimiento } from '../types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card';
import { Building2, TrendingUp, TrendingDown, DollarSign } from 'lucide-react';
import { getTransaccionesByEstablecimiento } from '../utils/storage';
import { calcularResumenMensual, formatearMoneda } from '../utils/calculations';

interface EstablecimientoCardProps {
  establecimiento: Establecimiento;
}

export function EstablecimientoCard({ establecimiento }: EstablecimientoCardProps) {
  const transacciones = getTransaccionesByEstablecimiento(establecimiento.id);
  const resumen = calcularResumenMensual(transacciones, new Date());

  return (
    <Link to={`/establecimiento/${establecimiento.id}`}>
      <Card className="hover:shadow-lg transition-shadow cursor-pointer">
        <CardHeader>
          <div className="flex items-start justify-between">
            <div className="flex items-center gap-3">
              <div className="p-2 bg-primary/10 rounded-lg">
                <Building2 className="h-6 w-6 text-primary" />
              </div>
              <div>
                <CardTitle>{establecimiento.nombre}</CardTitle>
                {establecimiento.descripcion && (
                  <CardDescription>{establecimiento.descripcion}</CardDescription>
                )}
              </div>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-3 gap-4">
            <div className="space-y-1">
              <div className="flex items-center gap-1 text-green-600">
                <TrendingUp className="h-4 w-4" />
                <span className="text-xs">Ingresos</span>
              </div>
              <p className="font-semibold">{formatearMoneda(resumen.totalIngresos)}</p>
            </div>
            <div className="space-y-1">
              <div className="flex items-center gap-1 text-red-600">
                <TrendingDown className="h-4 w-4" />
                <span className="text-xs">Gastos</span>
              </div>
              <p className="font-semibold">{formatearMoneda(resumen.totalGastos)}</p>
            </div>
            <div className="space-y-1">
              <div className="flex items-center gap-1">
                <DollarSign className="h-4 w-4" />
                <span className="text-xs">Balance</span>
              </div>
              <p className={`font-semibold ${resumen.balance >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                {formatearMoneda(resumen.balance)}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </Link>
  );
}
