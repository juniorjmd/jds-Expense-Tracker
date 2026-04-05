import { useState } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from './ui/dialog';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Textarea } from './ui/textarea';
import { Plus } from 'lucide-react';
import { saveEstablecimiento } from '../utils/storage';
import { Establecimiento } from '../types';
import { toast } from 'sonner';

interface NuevoEstablecimientoDialogProps {
  onCreated: () => void;
}

export function NuevoEstablecimientoDialog({ onCreated }: NuevoEstablecimientoDialogProps) {
  const [open, setOpen] = useState(false);
  const [nombre, setNombre] = useState('');
  const [descripcion, setDescripcion] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!nombre.trim()) {
      toast.error('El nombre es requerido');
      return;
    }

    const nuevoEstablecimiento: Establecimiento = {
      id: crypto.randomUUID(),
      nombre: nombre.trim(),
      descripcion: descripcion.trim(),
      createdAt: new Date().toISOString(),
    };

    saveEstablecimiento(nuevoEstablecimiento);
    toast.success('Establecimiento creado exitosamente');
    
    setNombre('');
    setDescripcion('');
    setOpen(false);
    onCreated();
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button>
          <Plus className="mr-2 h-4 w-4" />
          Nuevo Establecimiento
        </Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Crear Nuevo Establecimiento</DialogTitle>
          <DialogDescription>
            Agrega un nuevo establecimiento para gestionar sus gastos e ingresos
          </DialogDescription>
        </DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="nombre">Nombre *</Label>
            <Input
              id="nombre"
              placeholder="Ej: Tienda Principal"
              value={nombre}
              onChange={(e) => setNombre(e.target.value)}
              required
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="descripcion">Descripción</Label>
            <Textarea
              id="descripcion"
              placeholder="Descripción del establecimiento (opcional)"
              value={descripcion}
              onChange={(e) => setDescripcion(e.target.value)}
              rows={3}
            />
          </div>
          <div className="flex justify-end gap-2">
            <Button type="button" variant="outline" onClick={() => setOpen(false)}>
              Cancelar
            </Button>
            <Button type="submit">Crear Establecimiento</Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}
