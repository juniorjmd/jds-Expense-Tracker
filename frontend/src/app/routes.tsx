import { createBrowserRouter } from 'react-router';
import { Dashboard } from './pages/Dashboard';
import { EstablecimientoDetalle } from './pages/EstablecimientoDetalle';
import { Resumen } from './pages/Resumen';
import { Login } from './pages/Login';
import { Usuarios } from './pages/Usuarios';
import { ProtectedRoute } from './components/ProtectedRoute';

export const router = createBrowserRouter([
  {
    path: '/login',
    element: <Login />,
  },
  {
    path: '/',
    element: (
      <ProtectedRoute>
        <Dashboard />
      </ProtectedRoute>
    ),
  },
  {
    path: '/establecimiento/:id',
    element: (
      <ProtectedRoute>
        <EstablecimientoDetalle />
      </ProtectedRoute>
    ),
  },
  {
    path: '/resumen',
    element: (
      <ProtectedRoute>
        <Resumen />
      </ProtectedRoute>
    ),
  },
  {
    path: '/usuarios',
    element: (
      <ProtectedRoute>
        <Usuarios />
      </ProtectedRoute>
    ),
  },
]);