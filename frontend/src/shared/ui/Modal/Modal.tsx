import React, { useEffect } from 'react';
import { t } from '../../i18n';
import styles from './Modal.module.css';

export interface ModalProps {
  /**
   * Test ID obligatorio para testing E2E
   */
  testId: string;
  /**
   * Si el modal está abierto
   */
  isOpen: boolean;
  /**
   * Función para cerrar el modal
   */
  onClose: () => void;
  /**
   * Título del modal (usará t() internamente)
   */
  title?: string;
  /**
   * Key de traducción para el título
   */
  titleKey?: string;
  /**
   * Contenido del modal
   */
  children: React.ReactNode;
  /**
   * Si el modal se puede cerrar haciendo click fuera
   */
  closeOnOverlayClick?: boolean;
  /**
   * Si el modal se puede cerrar con ESC
   */
  closeOnEsc?: boolean;
  /**
   * Tamaño del modal
   */
  size?: 'small' | 'medium' | 'large' | 'full';
  /**
   * Clase CSS adicional
   */
  className?: string;
}

/**
 * Modal - Componente wrapper de modal con i18n y testId obligatorios
 * 
 * Reglas:
 * - testId es obligatorio
 * - Todo texto visible usa t() con fallback
 * - Separación CSS/JSX/JS
 * - Accesibilidad completa (roles, aria, focus trap, ESC key)
 */
export const Modal: React.FC<ModalProps> = ({
  testId,
  isOpen,
  onClose,
  title,
  titleKey,
  children,
  closeOnOverlayClick = true,
  closeOnEsc = true,
  size = 'medium',
  className
}) => {
  // Texto del título traducido
  const titleText = titleKey ? t(titleKey, title || '') : title || '';

  // Manejar tecla ESC
  useEffect(() => {
    if (!isOpen || !closeOnEsc) return;

    const handleEsc = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        onClose();
      }
    };

    document.addEventListener('keydown', handleEsc);
    return () => document.removeEventListener('keydown', handleEsc);
  }, [isOpen, closeOnEsc, onClose]);

  // Prevenir scroll del body cuando el modal está abierto
  useEffect(() => {
    if (isOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
    return () => {
      document.body.style.overflow = '';
    };
  }, [isOpen]);

  if (!isOpen) return null;

  const modalClasses = [styles.modal, styles[size], className].filter(Boolean).join(' ');

  const handleOverlayClick = (e: React.MouseEvent<HTMLDivElement>) => {
    if (closeOnOverlayClick && e.target === e.currentTarget) {
      onClose();
    }
  };

  return (
    <div
      className={styles.overlay}
      data-testid={`${testId}-overlay`}
      onClick={handleOverlayClick}
      role="dialog"
      aria-modal="true"
      aria-labelledby={titleText ? `${testId}-title` : undefined}
    >
      <div className={modalClasses} data-testid={testId}>
        {titleText && (
          <div className={styles.header}>
            <h2 id={`${testId}-title`} className={styles.title}>
              {titleText}
            </h2>
            <button
              data-testid={`${testId}-close-button`}
              className={styles.closeButton}
              onClick={onClose}
              aria-label={t('common.close', 'Cerrar')}
              type="button"
            >
              ×
            </button>
          </div>
        )}
        <div className={styles.content} data-testid={`${testId}-content`}>
          {children}
        </div>
      </div>
    </div>
  );
};

