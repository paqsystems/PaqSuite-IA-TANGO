/**
 * Component: EmpresaSelectorPage
 *
 * Pantalla para seleccionar empresa activa cuando el usuario tiene varias.
 * TR-002 (Selección empresa) - vista inicial post-login.
 *
 * @see docs/04-tareas/001-Seguridad/TR-002-seleccion-empresa.md
 */

import React from 'react';
import { useNavigate } from 'react-router-dom';
import { getEmpresas, setEmpresaActiva, EmpresaItem } from '../../../shared/utils/tokenStorage';
import './EmpresaSelectorPage.css';

export function EmpresaSelectorPage(): React.ReactElement {
  const navigate = useNavigate();
  const empresas = getEmpresas();

  const handleSelect = (empresa: EmpresaItem) => {
    setEmpresaActiva(empresa);
    navigate('/');
  };

  if (empresas.length === 0) {
    return (
      <div className="empresa-selector-page" data-testid="empresaSelector.empty">
        <p>No hay empresas asignadas. Contacte al administrador.</p>
        <button type="button" onClick={() => navigate('/login')}>
          Volver al login
        </button>
      </div>
    );
  }

  return (
    <div className="empresa-selector-page" data-testid="empresaSelector.page">
      <h1>Seleccione la empresa</h1>
      <div className="empresa-list" role="list">
        {empresas.map((emp) => (
          <button
            key={emp.id}
            type="button"
            className="empresa-card"
            onClick={() => handleSelect(emp)}
            data-testid={`empresaSelector.option.${emp.id}`}
          >
            {emp.nombreEmpresa}
          </button>
        ))}
      </div>
    </div>
  );
}

export default EmpresaSelectorPage;
