/**
 * Component: App
 *
 * Componente raíz de la aplicación.
 */

import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { LoginForm, ForgotPasswordPage, ResetPasswordPage, EmpresaSelectorPage } from '../features/auth';
import { ProfileView } from '../features/user';
import { UsersAdminPage, EmpresasAdminPage, GruposEmpresariosAdminPage, GrupoEmpresarioCrearPage, GrupoEmpresarioEditarPage, GrupoEmpresarioDetallePage, RolesAdminPage, RolAtributosPage, PermisosAdminPage } from '../features/admin';
import { ParametrosGeneralesPage } from '../features/parametros';
import { ProtectedRoute, PublicRoute } from '../routes';
import { AppLayout } from './AppLayout';
import { Dashboard } from './Dashboard';
import './App.css';

export function App(): React.ReactElement {
  return (
    <BrowserRouter>
      <Routes>
        <Route
          path="/login"
          element={
            <PublicRoute>
              <LoginForm />
            </PublicRoute>
          }
        />
        <Route
          path="/forgot-password"
          element={
            <PublicRoute>
              <ForgotPasswordPage />
            </PublicRoute>
          }
        />
        <Route
          path="/reset-password"
          element={
            <PublicRoute>
              <ResetPasswordPage />
            </PublicRoute>
          }
        />

        <Route
          path="/select-empresa"
          element={
            <ProtectedRoute>
              <EmpresaSelectorPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/"
          element={
            <ProtectedRoute>
              <AppLayout />
            </ProtectedRoute>
          }
        >
          <Route index element={<Dashboard />} />
          <Route path="perfil" element={<ProfileView />} />
          <Route path="admin/usuarios" element={<UsersAdminPage />} />
          <Route path="admin/empresas" element={<EmpresasAdminPage />} />
          <Route path="admin/grupos-empresarios" element={<GruposEmpresariosAdminPage />} />
          <Route path="admin/grupos-empresarios/crear" element={<GrupoEmpresarioCrearPage />} />
          <Route path="admin/grupos-empresarios/:id/editar" element={<GrupoEmpresarioEditarPage />} />
          <Route path="admin/grupos-empresarios/:id" element={<GrupoEmpresarioDetallePage />} />
          <Route path="admin/roles" element={<RolesAdminPage />} />
          <Route path="admin/roles/:id/atributos" element={<RolAtributosPage />} />
          <Route path="admin/permisos" element={<PermisosAdminPage />} />
          <Route path="parametros/:programa" element={<ParametrosGeneralesPage />} />
          <Route path="*" element={<Navigate to="/" replace />} />
        </Route>
      </Routes>
    </BrowserRouter>
  );
}

export default App;
