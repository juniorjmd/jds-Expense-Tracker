import { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router';
import { 
  getEstablecimientos, 
  getTransaccionesByEstablecimiento, 
  getGastosPredeterminadosByEstablecimiento,
  deleteTransaccion,
  deleteGastoPredeterminado,
  aplicarGastoPredeterminado,
  deleteEstablecimiento
} from '../utils/storage';
import { Establecimiento, Transaccion, GastoPredeterminado } from '../types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../components/ui/tabs';
import { NuevaTransaccionDialog } from '../components/NuevaTransaccionDialog';
import { GastoPredeterminadoDialog } from '../components/GastoPredeterminadoDialog';
import { ArrowLeft, Trash2, Calendar, TrendingUp, TrendingDown, DollarSign, CheckCircle2 } from 'lucide-react';
import { calcularResumenMensual, formatearMoneda } from '../utils/calculations';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import { toast } from 'sonner';
import { useAuth } from '../contexts/AuthContext';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '../components/ui/alert-dialog';

export function EstablecimientoDetalle() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [establecimiento, setEstablecimiento] = useState<Establecimiento | null>(null);
  const [transacciones, setTransacciones] = useState<Transaccion[]>([]);
  const [gastosPredeterminados, setGastosPredeterminados] = useState<GastoPredeterminado[]>([]);
  const [refreshKey, setRefreshKey] = useState(0);
  const { tienePermiso, tieneAccesoEstablecimiento } = useAuth();

  useEffect(() => {
    if (!id) return;
    
    // Verificar acceso al establecimiento
    if (!tieneAccesoEstablecimiento(id)) {
      toast.error('No tienes acceso a este establecimiento');
      navigate('/');
      return;
    }
    
    const establecimientos = getEstablecimientos();
    const encontrado = establecimientos.find(e => e.id === id);
    
    if (!encontrado) {
      navigate('/');
      return;
    }
    
    setEstablecimiento(encontrado);
    setTransacciones(getTransaccionesByEstablecimiento(id));
    setGastosPredeterminados(getGastosPredeterminadosByEstablecimiento(id));
  }, [id, navigate, refreshKey, tieneAccesoEstablecimiento]);

  if (!establecimiento) {
    return null;
  }

  const resumen = calcularResumenMensual(transacciones, new Date());

  const handleDeleteTransaccion = (transaccionId: string) => {
    deleteTransaccion(transaccionId);
    toast.success('Transacción eliminada');
    setRefreshKey(k => k + 1);
  };

  const handleDeleteGastoPredeterminado = (gastoId: string) => {
    deleteGastoPredeterminado(gastoId);
    toast.success('Gasto predeterminado eliminado');
    setRefreshKey(k => k + 1);
  };

  const handleAplicarGasto = (gasto: GastoPredeterminado) => {
    aplicarGastoPredeterminado(gasto);
    toast.success('Gasto aplicado exitosamente');
    setRefreshKey(k => k + 1);
  };

  const handleDeleteEstablecimiento = () => {
    deleteEstablecimiento(establecimiento.id);
    toast.success('Establecimiento eliminado');
    navigate('/');
  };

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
                <h1 className="text-3xl font-bold">{establecimiento.nombre}</h1>
                {establecimiento.descripcion && (
                  <p className="text-muted-foreground mt-1">{establecimiento.descripcion}</p>
                )}
              </div>
            </div>
            <AlertDialog>
              <AlertDialogTrigger asChild>
                <Button variant="destructive" size="icon">
                  <Trash2 className="h-4 w-4" />
                </Button>
              </AlertDialogTrigger>
              <AlertDialogContent>
                <AlertDialogHeader>
                  <AlertDialogTitle>¿Eliminar establecimiento?</AlertDialogTitle>
                  <AlertDialogDescription>
                    Esta acción eliminará el establecimiento y todas sus transacciones y gastos predeterminados.
                    Esta acción no se puede deshacer.
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                  <AlertDialogCancel>Cancelar</AlertDialogCancel>
                  <AlertDialogAction onClick={handleDeleteEstablecimiento}>
                    Eliminar
                  </AlertDialogAction>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>
          </div>
        </div>
      </div>

      <div className="container mx-auto px-4 py-8">
        {/* Resumen del Mes */}
        <div className="mb-8">
          <h2 className="text-xl font-semibold mb-4">Resumen del Mes Actual</h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Total Ingresos</CardTitle>
                <TrendingUp className="h-4 w-4 text-green-600" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold text-green-600">
                  {formatearMoneda(resumen.totalIngresos)}
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
                  {formatearMoneda(resumen.totalGastos)}
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Balance</CardTitle>
                <DollarSign className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className={`text-2xl font-bold ${resumen.balance >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                  {formatearMoneda(resumen.balance)}
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Tabs */}
        <Tabs defaultValue="transacciones" className="space-y-4">
          <TabsList>
            <TabsTrigger value="transacciones">Transacciones</TabsTrigger>
            <TabsTrigger value="predeterminados">Gastos Predeterminados</TabsTrigger>
          </TabsList>

          <TabsContent value="transacciones" className="space-y-4">
            <div className="flex justify-between items-center">
              <h3 className="text-lg font-semibold">Historial de Transacciones</h3>
              {tienePermiso('editar') && (
                <NuevaTransaccionDialog 
                  establecimientoId={establecimiento.id} 
                  onCreated={() => setRefreshKey(k => k + 1)}
                />
              )}
            </div>

            {transacciones.length === 0 ? (
              <Card className="p-12 text-center">
                <Calendar className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                <h3 className="text-lg font-semibold mb-2">No hay transacciones</h3>
                <p className="text-muted-foreground">
                  Registra tu primer ingreso o gasto
                </p>
              </Card>
            ) : (
              <div className="space-y-2">
                {transacciones
                  .sort((a, b) => new Date(b.fecha).getTime() - new Date(a.fecha).getTime())
                  .map(transaccion => (
                    <Card key={transaccion.id}>
                      <CardContent className="flex items-center justify-between p-4">
                        <div className="flex items-start gap-3">
                          <div className={`p-2 rounded-lg ${transaccion.tipo === 'ingreso' ? 'bg-green-100' : 'bg-red-100'}`}>
                            {transaccion.tipo === 'ingreso' ? (
                              <TrendingUp className="h-5 w-5 text-green-600" />
                            ) : (
                              <TrendingDown className="h-5 w-5 text-red-600" />
                            )}
                          </div>
                          <div>
                            <div className="flex items-center gap-2">
                              <p className="font-semibold">{transaccion.categoria}</p>
                              {transaccion.esPredeterminado && (
                                <CheckCircle2 className="h-4 w-4 text-muted-foreground" />
                              )}
                            </div>
                            <p className="text-sm text-muted-foreground">{transaccion.descripcion}</p>
                            <p className="text-xs text-muted-foreground mt-1">
                              {format(new Date(transaccion.fecha), "d 'de' MMMM, yyyy", { locale: es })}
                            </p>
                          </div>
                        </div>
                        <div className="flex items-center gap-4">
                          <p className={`text-lg font-bold ${transaccion.tipo === 'ingreso' ? 'text-green-600' : 'text-red-600'}`}>
                            {transaccion.tipo === 'ingreso' ? '+' : '-'}{formatearMoneda(transaccion.monto)}
                          </p>
                          <AlertDialog>
                            <AlertDialogTrigger asChild>
                              <Button variant="ghost" size="icon">
                                <Trash2 className="h-4 w-4" />
                              </Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                              <AlertDialogHeader>
                                <AlertDialogTitle>¿Eliminar transacción?</AlertDialogTitle>
                                <AlertDialogDescription>
                                  Esta acción no se puede deshacer.
                                </AlertDialogDescription>
                              </AlertDialogHeader>
                              <AlertDialogFooter>
                                <AlertDialogCancel>Cancelar</AlertDialogCancel>
                                <AlertDialogAction onClick={() => handleDeleteTransaccion(transaccion.id)}>
                                  Eliminar
                                </AlertDialogAction>
                              </AlertDialogFooter>
                            </AlertDialogContent>
                          </AlertDialog>
                        </div>
                      </CardContent>
                    </Card>
                  ))}
              </div>
            )}
          </TabsContent>

          <TabsContent value="predeterminados" className="space-y-4">
            <div className="flex justify-between items-center">
              <div>
                <h3 className="text-lg font-semibold">Gastos Predeterminados</h3>
                <p className="text-sm text-muted-foreground">
                  Define gastos recurrentes para aplicarlos rápidamente
                </p>
              </div>
              <GastoPredeterminadoDialog 
                establecimientoId={establecimiento.id} 
                onCreated={() => setRefreshKey(k => k + 1)}
              />
            </div>

            {gastosPredeterminados.length === 0 ? (
              <Card className="p-12 text-center">
                <DollarSign className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                <h3 className="text-lg font-semibold mb-2">No hay gastos predeterminados</h3>
                <p className="text-muted-foreground">
                  Crea gastos predeterminados para aplicarlos rápidamente
                </p>
              </Card>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {gastosPredeterminados.map(gasto => (
                  <Card key={gasto.id}>
                    <CardHeader>
                      <CardTitle className="text-base">{gasto.categoria}</CardTitle>
                      <CardDescription>{gasto.descripcion}</CardDescription>
                    </CardHeader>
                    <CardContent>
                      <div className="flex items-center justify-between">
                        <p className="text-2xl font-bold">{formatearMoneda(gasto.monto)}</p>
                        <div className="flex gap-2">
                          <Button 
                            variant="outline" 
                            size="sm"
                            onClick={() => handleAplicarGasto(gasto)}
                          >
                            Aplicar
                          </Button>
                          <AlertDialog>
                            <AlertDialogTrigger asChild>
                              <Button variant="ghost" size="icon">
                                <Trash2 className="h-4 w-4" />
                              </Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                              <AlertDialogHeader>
                                <AlertDialogTitle>¿Eliminar gasto predeterminado?</AlertDialogTitle>
                                <AlertDialogDescription>
                                  Esta acción no se puede deshacer.
                                </AlertDialogDescription>
                              </AlertDialogHeader>
                              <AlertDialogFooter>
                                <AlertDialogCancel>Cancelar</AlertDialogCancel>
                                <AlertDialogAction onClick={() => handleDeleteGastoPredeterminado(gasto.id)}>
                                  Eliminar
                                </AlertDialogAction>
                              </AlertDialogFooter>
                            </AlertDialogContent>
                          </AlertDialog>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
}