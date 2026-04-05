import { useState, useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { useNavigate } from 'react-router';
import { getUsuarios, crearUsuario, eliminarUsuario, actualizarUsuario } from '../utils/auth';
import { getEstablecimientos } from '../utils/storage';
import { Usuario, RolUsuario, Establecimiento } from '../types';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '../components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../components/ui/select';
import { UserPlus, Trash2, Edit, Users, ArrowLeft } from 'lucide-react';
import { Link } from 'react-router';

export function Usuarios() {
  const { tienePermiso } = useAuth();
  const navigate = useNavigate();
  const [usuarios, setUsuarios] = useState<Usuario[]>([]);
  const [establecimientos, setEstablecimientos] = useState<Establecimiento[]>([]);
  const [openDialog, setOpenDialog] = useState(false);
  const [editingUser, setEditingUser] = useState<Usuario | null>(null);

  // Form state
  const [nombre, setNombre] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [rol, setRol] = useState<RolUsuario>('visualizador');
  const [establecimientosSeleccionados, setEstablecimientosSeleccionados] = useState<string[]>([]);

  useEffect(() => {
    if (!tienePermiso('gestionar_usuarios')) {
      navigate('/');
      return;
    }
    cargarDatos();
  }, [tienePermiso, navigate]);

  const cargarDatos = () => {
    setUsuarios(getUsuarios());
    setEstablecimientos(getEstablecimientos());
  };

  const resetForm = () => {
    setNombre('');
    setEmail('');
    setPassword('');
    setRol('visualizador');
    setEstablecimientosSeleccionados([]);
    setEditingUser(null);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    try {
      if (editingUser) {
        actualizarUsuario(editingUser.id, {
          nombre,
          email,
          ...(password && { password }),
          rol,
          establecimientosAsignados: establecimientosSeleccionados,
        });
      } else {
        crearUsuario(nombre, email, password, rol, establecimientosSeleccionados);
      }
      
      cargarDatos();
      setOpenDialog(false);
      resetForm();
    } catch (error) {
      alert(error instanceof Error ? error.message : 'Error al guardar usuario');
    }
  };

  const handleEdit = (usuario: Usuario) => {
    setEditingUser(usuario);
    setNombre(usuario.nombre);
    setEmail(usuario.email);
    setPassword('');
    setRol(usuario.rol);
    setEstablecimientosSeleccionados(usuario.establecimientosAsignados);
    setOpenDialog(true);
  };

  const handleDelete = (usuarioId: string) => {
    if (confirm('¿Estás seguro de eliminar este usuario?')) {
      eliminarUsuario(usuarioId);
      cargarDatos();
    }
  };

  const toggleEstablecimiento = (estabId: string) => {
    setEstablecimientosSeleccionados((prev) =>
      prev.includes(estabId)
        ? prev.filter((id) => id !== estabId)
        : [...prev, estabId]
    );
  };

  const getRolBadgeColor = (rol: RolUsuario) => {
    switch (rol) {
      case 'administrador':
        return 'bg-purple-100 text-purple-800';
      case 'editor':
        return 'bg-blue-100 text-blue-800';
      case 'visualizador':
        return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="bg-white border-b">
        <div className="container mx-auto px-4 py-6">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <Link to="/">
                <Button variant="ghost" size="icon">
                  <ArrowLeft className="h-5 w-5" />
                </Button>
              </Link>
              <div>
                <h1 className="text-2xl font-bold flex items-center gap-2">
                  <Users className="h-6 w-6" />
                  Gestión de Usuarios
                </h1>
                <p className="text-sm text-gray-500">Administra usuarios y permisos del sistema</p>
              </div>
            </div>

            <Dialog open={openDialog} onOpenChange={(open) => {
              setOpenDialog(open);
              if (!open) resetForm();
            }}>
              <DialogTrigger asChild>
                <Button>
                  <UserPlus className="h-4 w-4" />
                  Nuevo Usuario
                </Button>
              </DialogTrigger>
              <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                  <DialogTitle>
                    {editingUser ? 'Editar Usuario' : 'Crear Nuevo Usuario'}
                  </DialogTitle>
                </DialogHeader>
                <form onSubmit={handleSubmit} className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="nombre">Nombre</Label>
                    <Input
                      id="nombre"
                      value={nombre}
                      onChange={(e) => setNombre(e.target.value)}
                      required
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="email">Email</Label>
                    <Input
                      id="email"
                      type="email"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      required
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="password">
                      Contraseña {editingUser && '(dejar vacío para no cambiar)'}
                    </Label>
                    <Input
                      id="password"
                      type="password"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      required={!editingUser}
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="rol">Rol</Label>
                    <Select value={rol} onValueChange={(value) => setRol(value as RolUsuario)}>
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="administrador">Administrador</SelectItem>
                        <SelectItem value="editor">Editor</SelectItem>
                        <SelectItem value="visualizador">Visualizador</SelectItem>
                      </SelectContent>
                    </Select>
                    <p className="text-xs text-gray-500">
                      {rol === 'administrador' && 'Acceso completo a todos los establecimientos y funciones'}
                      {rol === 'editor' && 'Puede crear y editar en establecimientos asignados'}
                      {rol === 'visualizador' && 'Solo puede ver establecimientos asignados'}
                    </p>
                  </div>

                  {rol !== 'administrador' && (
                    <div className="space-y-2">
                      <Label>Establecimientos Asignados</Label>
                      <div className="border rounded-md p-4 space-y-2 max-h-48 overflow-y-auto">
                        {establecimientos.length === 0 ? (
                          <p className="text-sm text-gray-500">No hay establecimientos disponibles</p>
                        ) : (
                          establecimientos.map((estab) => (
                            <label key={estab.id} className="flex items-center gap-2 cursor-pointer">
                              <input
                                type="checkbox"
                                checked={establecimientosSeleccionados.includes(estab.id)}
                                onChange={() => toggleEstablecimiento(estab.id)}
                                className="rounded border-gray-300"
                              />
                              <span className="text-sm">{estab.nombre}</span>
                            </label>
                          ))
                        )}
                      </div>
                    </div>
                  )}

                  <div className="flex justify-end gap-2 pt-4">
                    <Button type="button" variant="outline" onClick={() => {
                      setOpenDialog(false);
                      resetForm();
                    }}>
                      Cancelar
                    </Button>
                    <Button type="submit">
                      {editingUser ? 'Actualizar' : 'Crear'} Usuario
                    </Button>
                  </div>
                </form>
              </DialogContent>
            </Dialog>
          </div>
        </div>
      </div>

      {/* Lista de Usuarios */}
      <div className="container mx-auto px-4 py-8">
        <div className="grid gap-4">
          {usuarios.map((usuario) => (
            <Card key={usuario.id}>
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div className="flex-1">
                    <div className="flex items-center gap-3 mb-2">
                      <h3 className="text-lg font-semibold">{usuario.nombre}</h3>
                      <span className={`text-xs px-2 py-1 rounded-full font-medium ${getRolBadgeColor(usuario.rol)}`}>
                        {usuario.rol}
                      </span>
                    </div>
                    <p className="text-sm text-gray-600 mb-2">{usuario.email}</p>
                    {usuario.rol !== 'administrador' && (
                      <div className="text-sm text-gray-500">
                        <span className="font-medium">Establecimientos: </span>
                        {usuario.establecimientosAsignados.length === 0 ? (
                          'Ninguno asignado'
                        ) : (
                          usuario.establecimientosAsignados
                            .map((id) => establecimientos.find((e) => e.id === id)?.nombre)
                            .filter(Boolean)
                            .join(', ')
                        )}
                      </div>
                    )}
                  </div>

                  <div className="flex gap-2">
                    <Button
                      variant="outline"
                      size="icon"
                      onClick={() => handleEdit(usuario)}
                    >
                      <Edit className="h-4 w-4" />
                    </Button>
                    {usuario.id !== '1' && ( // No permitir eliminar al admin por defecto
                      <Button
                        variant="outline"
                        size="icon"
                        onClick={() => handleDelete(usuario.id)}
                      >
                        <Trash2 className="h-4 w-4 text-red-600" />
                      </Button>
                    )}
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </div>
  );
}
