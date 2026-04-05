import { useState } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from './ui/dialog';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Textarea } from './ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from './ui/select';
import { Plus } from 'lucide-react';
import { saveTransaccion } from '../utils/storage';
import { Transaccion } from '../types';
import { toast } from 'sonner';

interface NuevaTransaccionDialogProps {
  establecimientoId: string;
  onCreated: () => void;
  trigger?: React.ReactNode;
}

export function NuevaTransaccionDialog({ establecimientoId, onCreated, trigger }: NuevaTransaccionDialogProps) {
  const [open, setOpen] = useState(false);
  const [tipo, setTipo] = useState<'ingreso' | 'gasto'>('gasto');
  const [monto, setMonto] = useState('');
  const [categoria, setCategoria] = useState('');
  const [descripcion, setDescripcion] = useState('');
  const [fecha, setFecha] = useState(new Date().toISOString().split('T')[0]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!monto || parseFloat(monto) <= 0) {
      toast.error('El monto debe ser mayor a 0');
      return;
    }

    if (!categoria.trim()) {
      toast.error('La categoría es requerida');
      return;
    }

    const nuevaTransaccion: Transaccion = {
      id: crypto.randomUUID(),
      establecimientoId,
      tipo,
      monto: parseFloat(monto),
      categoria: categoria.trim(),
      descripcion: descripcion.trim(),
      fecha: new Date(fecha).toISOString(),
    };

    saveTransaccion(nuevaTransaccion);
    toast.success(`${tipo === 'ingreso' ? 'Ingreso' : 'Gasto'} registrado exitosamente`);
    
    setMonto('');
    setCategoria('');
    setDescripcion('');
    setFecha(new Date().toISOString().split('T')[0]);
    setOpen(false);
    onCreated();
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        {trigger || (
          <Button>
            <Plus className="mr-2 h-4 w-4" />
            Nueva Transacción
          </Button>
        )}
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Registrar Transacción</DialogTitle>
          <DialogDescription>
            Agrega un nuevo ingreso o gasto para este establecimiento
          </DialogDescription>
        </DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="tipo">Tipo *</Label>
            <Select value={tipo} onValueChange={(value: 'ingreso' | 'gasto') => setTipo(value)}>
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="ingreso">Ingreso</SelectItem>
                <SelectItem value="gasto">Gasto</SelectItem>
              </SelectContent>
            </Select>
          </div>
          
          <div className="space-y-2">
            <Label htmlFor="monto">Monto *</Label>
            <Input
              id="monto"
              type="number"
              step="0.01"
              min="0.01"
              placeholder="0.00"
              value={monto}
              onChange={(e) => setMonto(e.target.value)}
              required
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="categoria">Categoría *</Label>
            <Input
              id="categoria"
              placeholder="Ej: Ventas, Alquiler, Servicios"
              value={categoria}
              onChange={(e) => setCategoria(e.target.value)}
              required
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="descripcion">Descripción</Label>
            <Textarea
              id="descripcion"
              placeholder="Detalles adicionales (opcional)"
              value={descripcion}
              onChange={(e) => setDescripcion(e.target.value)}
              rows={2}
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="fecha">Fecha *</Label>
            <Input
              id="fecha"
              type="date"
              value={fecha}
              onChange={(e) => setFecha(e.target.value)}
              required
            />
          </div>

          <div className="flex justify-end gap-2">
            <Button type="button" variant="outline" onClick={() => setOpen(false)}>
              Cancelar
            </Button>
            <Button type="submit">Registrar</Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}
