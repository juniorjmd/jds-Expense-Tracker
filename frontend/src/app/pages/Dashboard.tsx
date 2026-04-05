import { useState, useEffect } from 'react';
import { NuevoEstablecimientoDialog } from '../components/NuevoEstablecimientoDialog';
import { EstablecimientoCard } from '../components/EstablecimientoCard';
import { getEstablecimientos, getTransacciones } from '../utils/storage';
import { calcularResumenMensual, formatearMoneda } from '../utils/calculations';
import { Establecimiento } from '../types';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Building2, TrendingUp, TrendingDown, DollarSign, PieChart, LogOut, Users } from 'lucide-react';
import { Link, useNavigate } from 'react-router';
import { Button } from '../components/ui/button';
import { useAuth } from '../contexts/AuthContext';

export function Dashboard() {
  const [establecimientos, setEstablecimientos] = useState<Establecimiento[]>([]);
  const [refreshKey, setRefreshKey] = useState(0);
  const { usuario, logout, tienePermiso, tieneAccesoEstablecimiento } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    const todosEstablecimientos = getEstablecimientos();
    
    // Filtrar establecimientos según permisos del usuario
    const establecimientosFiltrados = todosEstablecimientos.filter((estab) =>
      tieneAccesoEstablecimiento(estab.id)
    );
    
    setEstablecimientos(establecimientosFiltrados);
  }, [refreshKey, tieneAccesoEstablecimiento]);

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  // Filtrar transacciones según establecimientos accesibles
  const todasTransacciones = getTransacciones();
  const transacciones = todasTransacciones.filter((t) =>
    tieneAccesoEstablecimiento(t.establecimientoId)
  );
  const resumenGeneral = calcularResumenMensual(transacciones, new Date());

  return (
    <div className="min-h-screen bg-background">
      <div className="border-b">
        <div className="container mx-auto px-4 py-6">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold">Gestión Financiera</h1>
              <p className="text-muted-foreground mt-1">
                Control de ingresos y gastos por establecimiento
              </p>
              <p className="text-sm text-gray-500 mt-1">
                Usuario: <span className="font-medium">{usuario?.nombre}</span> ({usuario?.rol})
              </p>
            </div>
            <div className="flex gap-2">
              {tienePermiso('gestionar_usuarios') && (
                <Link to="/usuarios">
                  <Button variant="outline">
                    <Users className="mr-2 h-4 w-4" />
                    Usuarios
                  </Button>
                </Link>
              )}
              {tienePermiso('ver_resumen') && (
                <Link to="/resumen">
                  <Button variant="outline">
                    <PieChart className="mr-2 h-4 w-4" />
                    Ver Resúmenes
                  </Button>
                </Link>
              )}
              {tienePermiso('crear') && (
                <NuevoEstablecimientoDialog onCreated={() => setRefreshKey(k => k + 1)} />
              )}
              <Button variant="outline" onClick={handleLogout}>
                <LogOut className="mr-2 h-4 w-4" />
                Salir
              </Button>
            </div>
          </div>
        </div>
      </div>

      <div className="container mx-auto px-4 py-8">
        {/* Resumen General del Mes */}
        <div className="mb-8">
          <h2 className="text-xl font-semibold mb-4">Resumen del Mes Actual</h2>
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Establecimientos</CardTitle>
                <Building2 className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{establecimientos.length}</div>
              </CardContent>
            </Card>

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
                <CardTitle className="text-sm font-medium">Balance</CardTitle>
                <DollarSign className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className={`text-2xl font-bold ${resumenGeneral.balance >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                  {formatearMoneda(resumenGeneral.balance)}
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Lista de Establecimientos */}
        <div>
          <h2 className="text-xl font-semibold mb-4">Mis Establecimientos</h2>
          {establecimientos.length === 0 ? (
            <Card className="p-12 text-center">
              <Building2 className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
              <h3 className="text-lg font-semibold mb-2">No hay establecimientos</h3>
              <p className="text-muted-foreground mb-4">
                Comienza creando tu primer establecimiento para gestionar ingresos y gastos
              </p>
              <NuevoEstablecimientoDialog onCreated={() => setRefreshKey(k => k + 1)} />
            </Card>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {establecimientos.map(establecimiento => (
                <EstablecimientoCard key={establecimiento.id} establecimiento={establecimiento} />
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}