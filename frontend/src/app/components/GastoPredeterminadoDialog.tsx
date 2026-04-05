import { useState } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from './ui/dialog';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Textarea } from './ui/textarea';
import { ListPlus } from 'lucide-react';
import { saveGastoPredeterminado } from '../utils/storage';
import { GastoPredeterminado } from '../types';
import { toast } from 'sonner';

interface GastoPredeterminadoDialogProps {
  establecimientoId: string;
  onCreated: () => void;
}

export function GastoPredeterminadoDialog({ establecimientoId, onCreated }: GastoPredeterminadoDialogProps) {
  const [open, setOpen] = useState(false);
  const [categoria, setCategoria] = useState('');
  const [descripcion, setDescripcion] = useState('');
  const [monto, setMonto] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!categoria.trim()) {
      toast.error('La categoría es requerida');
      return;
    }

    if (!monto || parseFloat(monto) <= 0) {
      toast.error('El monto debe ser mayor a 0');
      return;
    }

    const nuevoGasto: GastoPredeterminado = {
      id: crypto.randomUUID(),
      establecimientoId,
      categoria: categoria.trim(),
      descripcion: descripcion.trim(),
      monto: parseFloat(monto),
    };

    saveGastoPredeterminado(nuevoGasto);
    toast.success('Gasto predeterminado creado exitosamente');
    
    setCategoria('');
    setDescripcion('');
    setMonto('');
    setOpen(false);
    onCreated();
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="outline">
          <ListPlus className="mr-2 h-4 w-4" />
          Nuevo Gasto Predeterminado
        </Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Crear Gasto Predeterminado</DialogTitle>
          <DialogDescription>
            Define un gasto recurrente que puedes aplicar rápidamente
          </DialogDescription>
        </DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="categoria">Categoría *</Label>
            <Input
              id="categoria"
              placeholder="Ej: Alquiler, Servicios, Nómina"
              value={categoria}
              onChange={(e) => setCategoria(e.target.value)}
              required
            />
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
            <Label htmlFor="descripcion">Descripción</Label>
            <Textarea
              id="descripcion"
              placeholder="Detalles del gasto predeterminado (opcional)"
              value={descripcion}
              onChange={(e) => setDescripcion(e.target.value)}
              rows={2}
            />
          </div>

          <div className="flex justify-end gap-2">
            <Button type="button" variant="outline" onClick={() => setOpen(false)}>
              Cancelar
            </Button>
            <Button type="submit">Crear</Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}
