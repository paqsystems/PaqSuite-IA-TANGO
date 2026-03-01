/**
 * CompanySwitcher – Selector de empresa activa en header
 *
 * @see docs/04-tareas/001-Seguridad/TR-002-seleccion-empresa.md
 */

import React, { useState, useRef, useEffect } from 'react';
import { getEmpresaActiva, getEmpresas, setEmpresaActiva, EmpresaItem } from '../../../shared/utils/tokenStorage';
import { t } from '../../../shared/i18n';
import './CompanySwitcher.css';

export function CompanySwitcher(): React.ReactElement | null {
  const [isOpen, setIsOpen] = useState(false);
  const [empresaActiva, setEmpresaActivaState] = useState<EmpresaItem | null>(() => getEmpresaActiva());
  const containerRef = useRef<HTMLDivElement>(null);

  const empresas = getEmpresas();

  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
        setIsOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  if (empresas.length <= 1) {
    return (
      <span className="company-switcher-single" data-testid="companySwitcher">
        {empresaActiva?.nombreEmpresa ?? empresas[0]?.nombreEmpresa ?? t('company.default', 'Empresa')}
      </span>
    );
  }

  const handleSelect = (empresa: EmpresaItem) => {
    setEmpresaActiva(empresa);
    setEmpresaActivaState(empresa);
    setIsOpen(false);
    window.location.reload();
  };

  return (
    <div className="company-switcher" ref={containerRef} data-testid="companySwitcher">
      <button
        type="button"
        className="company-switcher-trigger"
        onClick={() => setIsOpen((v) => !v)}
        aria-expanded={isOpen}
        aria-haspopup="listbox"
        aria-label={t('company.selectAria', 'Seleccionar empresa')}
      >
        <span>{empresaActiva?.nombreEmpresa ?? t('company.select', 'Seleccionar empresa')}</span>
        <span className="company-switcher-chevron" aria-hidden>
          {isOpen ? '▲' : '▼'}
        </span>
      </button>
      {isOpen && (
        <ul
          className="company-switcher-dropdown"
          role="listbox"
          aria-label={t('company.listAria', 'Lista de empresas')}
        >
          {empresas.map((emp) => (
            <li key={emp.id} role="option">
              <button
                type="button"
                className={`company-switcher-option ${empresaActiva?.id === emp.id ? 'active' : ''}`}
                onClick={() => handleSelect(emp)}
                data-testid={`companySwitcher.option.${emp.id}`}
              >
                {emp.nombreEmpresa}
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
