import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router';
import { getEstablecimientos, getTransacciones, getTransaccionesByEstablecimiento } from '../utils/storage';
import { calcularResumenMensual, obtenerMesesDisponibles, formatearMoneda } from '../utils/calculations';
import { Establecimiento } from '../types';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../components/ui/select';
import { ArrowLeft, TrendingUp, TrendingDown } from 'lucide-react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import { useAuth } from '../contexts/AuthContext';

const COLORS = ['#22c55e', '#ef4444', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899'];

export function Resumen() {
  const [establecimientos, setEstablecimientos] = useState<Establecimiento[]>([]);
  const [mesSeleccionado, setMesSeleccionado] = useState<Date>(new Date());
  const [mesesDisponibles, setMesesDisponibles] = useState<Date[]>([]);
  const { tienePermiso, tieneAccesoEstablecimiento } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    // Verificar permisos
    if (!tienePermiso('ver_resumen')) {
      navigate('/');
      return;
    }

    // Filtrar establecimientos según permisos
    const todosEstablecimientos = getEstablecimientos();
    const establecimientosFiltrados = todosEstablecimientos.filter((estab) =>
      tieneAccesoEstablecimiento(estab.id)
    );
    setEstablecimientos(establecimientosFiltrados);

    // Filtrar transacciones según permisos
    const todasTransacciones = getTransacciones();
    const transaccionesFiltradas = todasTransacciones.filter((t) =>
      tieneAccesoEstablecimiento(t.establecimientoId)
    );

    const meses = obtenerMesesDisponibles(transaccionesFiltradas);
    setMesesDisponibles(meses);
    if (meses.length > 0) {
      setMesSeleccionado(meses[0]);
    }
  }, [tienePermiso, tieneAccesoEstablecimiento, navigate]);

  // Filtrar transacciones según permisos
  const todasTransacciones = getTransacciones();
  const transaccionesFiltradas = todasTransacciones.filter((t) =>
    tieneAccesoEstablecimiento(t.establecimientoId)
  );
  const resumenGeneral = calcularResumenMensual(transaccionesFiltradas, mesSeleccionado);

  // Datos por establecimiento
  const datosEstablecimientos = establecimientos.map(est => {
    const transacciones = getTransaccionesByEstablecimiento(est.id);
    const resumen = calcularResumenMensual(transacciones, mesSeleccionado);
    return {
      nombre: est.nombre,
      ingresos: resumen.totalIngresos,
      gastos: resumen.totalGastos,
      balance: resumen.balance,
    };
  });

  // Datos para gráfico de barras
  const datosGraficoBarras = datosEstablecimientos.map(d => ({
    establecimiento: d.nombre.length > 15 ? d.nombre.substring(0, 15) + '...' : d.nombre,
    Ingresos: d.ingresos,
    Gastos: d.gastos,
  }));

  // Datos para gráfico de pastel (distribución de gastos por establecimiento)
  const datosGraficoPastelGastos = datosEstablecimientos
    .filter(d => d.gastos > 0)
    .map(d => ({
      name: d.nombre,
      value: d.gastos,
    }));

  // Datos para gráfico de pastel (distribución de ingresos por establecimiento)
  const datosGraficoPastelIngresos = datosEstablecimientos
    .filter(d => d.ingresos > 0)
    .map(d => ({
      name: d.nombre,
      value: d.ingresos,
    }));

  return (
    <div className="min-h-screen bg-background">
      <div className="border-b">
        <div className="container mx-auto px-4 py-6">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <Link to="/">
                <Button variant="ghost" size="icon">
                  <ArrowLeft className="h-5 w-5" />
                </Button>
              </Link>
              <div>
                <h1 className="text-3xl font-bold">Resúmenes Mensuales</h1>
                <p className="text-muted-foreground mt-1">
                  Análisis detallado de ingresos y gastos
                </p>
              </div>
            </div>
            <div className="w-64">
              <Select 
                value={format(mesSeleccionado, 'yyyy-MM')} 
                onValueChange={(value) => setMesSeleccionado(new Date(value + '-01'))}
              >
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {mesesDisponibles.map(mes => (
                    <SelectItem key={format(mes, 'yyyy-MM')} value={format(mes, 'yyyy-MM')}>
                      {format(mes, 'MMMM yyyy', { locale: es })}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>
      </div>

      <div className="container mx-auto px-4 py-8">
        {/* Resumen General */}
        <div className="mb-8">
          <h2 className="text-xl font-semibold mb-4">Resumen General - {resumenGeneral.mes}</h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Total Ingresos</CardTitle>
                <TrendingUp className="h-4 w-4 text-green-600" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold text-green-600">
                  {formatearMoneda(resumenGeneral.totalIngresos)}
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Total Gastos</CardTitle>
                <TrendingDown className="h-4 w-4 text-red-600" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold text-red-600">
                  {formatearMoneda(resumenGeneral.totalGastos)}
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Balance Neto</CardTitle>
              </CardHeader>
              <CardContent>
                <div className={`text-2xl font-bold ${resumenGeneral.balance >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                  {formatearMoneda(resumenGeneral.balance)}
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Gráficos */}
        {establecimientos.length > 0 && (
          <div className="space-y-8">
            {/* Gráfico de Barras */}
            <Card>
              <CardHeader>
                <CardTitle>Ingresos vs Gastos por Establecimiento</CardTitle>
              </CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={350}>
                  <BarChart data={datosGraficoBarras}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="establecimiento" />
                    <YAxis />
                    <Tooltip formatter={(value) => formatearMoneda(value as number)} />
                    <Legend />
                    <Bar dataKey="Ingresos" fill="#22c55e" />
                    <Bar dataKey="Gastos" fill="#ef4444" />
                  </BarChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>

            {/* Gráficos de Pastel */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {datosGraficoPastelIngresos.length > 0 && (
                <Card>
                  <CardHeader>
                    <CardTitle>Distribución de Ingresos</CardTitle>
                  </CardHeader>
                  <CardContent>
                    <ResponsiveContainer width="100%" height={300}>
                      <PieChart>
                        <Pie
                          data={datosGraficoPastelIngresos}
                          cx="50%"
                          cy="50%"
                          labelLine={false}
                          label={({ name, percent }) => `${name}: ${(percent * 100).toFixed(0)}%`}
                          outerRadius={80}
                          fill="#8884d8"
                          dataKey="value"
                        >
                          {datosGraficoPastelIngresos.map((entry, index) => (
                            <Cell key={`cell-ingresos-${index}`} fill={COLORS[index % COLORS.length]} />
                          ))}
                        </Pie>
                        <Tooltip formatter={(value) => formatearMoneda(value as number)} />
                      </PieChart>
                    </ResponsiveContainer>
                  </CardContent>
                </Card>
              )}

              {datosGraficoPastelGastos.length > 0 && (
                <Card>
                  <CardHeader>
                    <CardTitle>Distribución de Gastos</CardTitle>
                  </CardHeader>
                  <CardContent>
                    <ResponsiveContainer width="100%" height={300}>
                      <PieChart>
                        <Pie
                          data={datosGraficoPastelGastos}
                          cx="50%"
                          cy="50%"
                          labelLine={false}
                          label={({ name, percent }) => `${name}: ${(percent * 100).toFixed(0)}%`}
                          outerRadius={80}
                          fill="#8884d8"
                          dataKey="value"
                        >
                          {datosGraficoPastelGastos.map((entry, index) => (
                            <Cell key={`cell-gastos-${index}`} fill={COLORS[index % COLORS.length]} />
                          ))}
                        </Pie>
                        <Tooltip formatter={(value) => formatearMoneda(value as number)} />
                      </PieChart>
                    </ResponsiveContainer>
                  </CardContent>
                </Card>
              )}
            </div>

            {/* Tabla Detallada */}
            <Card>
              <CardHeader>
                <CardTitle>Detalle por Establecimiento</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="overflow-x-auto">
                  <table className="w-full">
                    <thead>
                      <tr className="border-b">
                        <th className="text-left p-3">Establecimiento</th>
                        <th className="text-right p-3">Ingresos</th>
                        <th className="text-right p-3">Gastos</th>
                        <th className="text-right p-3">Balance</th>
                      </tr>
                    </thead>
                    <tbody>
                      {datosEstablecimientos.map((dato, index) => (
                        <tr key={index} className="border-b hover:bg-muted/50">
                          <td className="p-3 font-medium">{dato.nombre}</td>
                          <td className="p-3 text-right text-green-600">
                            {formatearMoneda(dato.ingresos)}
                          </td>
                          <td className="p-3 text-right text-red-600">
                            {formatearMoneda(dato.gastos)}
                          </td>
                          <td className={`p-3 text-right font-semibold ${dato.balance >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                            {formatearMoneda(dato.balance)}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                    <tfoot>
                      <tr className="font-bold">
                        <td className="p-3">TOTAL</td>
                        <td className="p-3 text-right text-green-600">
                          {formatearMoneda(resumenGeneral.totalIngresos)}
                        </td>
                        <td className="p-3 text-right text-red-600">
                          {formatearMoneda(resumenGeneral.totalGastos)}
                        </td>
                        <td className={`p-3 text-right ${resumenGeneral.balance >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                          {formatearMoneda(resumenGeneral.balance)}
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </CardContent>
            </Card>
          </div>
        )}

        {establecimientos.length === 0 && (
          <Card className="p-12 text-center">
            <h3 className="text-lg font-semibold mb-2">No hay datos disponibles</h3>
            <p className="text-muted-foreground">
              Crea establecimientos y registra transacciones para ver los resúmenes
            </p>
          </Card>
        )}
      </div>
    </div>
  );
}