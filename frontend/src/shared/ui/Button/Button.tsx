import React from 'react';
import { t } from '../../i18n';
import styles from './Button.module.css';

export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  /**
   * Test ID obligatorio para testing E2E
   */
  testId: string;
  /**
   * Texto del botón (usará t() internamente si se proporciona)
   * Si se proporciona children, este se usa como fallback para aria-label
   */
  label?: string;
  /**
   * Key de traducción para el texto del botón
   */
  labelKey?: string;
  /**
   * Variante visual del botón
   */
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost';
  /**
   * Tamaño del botón
   */
  size?: 'small' | 'medium' | 'large';
  /**
   * Si es true, el botón ocupa todo el ancho disponible
   */
  fullWidth?: boolean;
  /**
   * Children opcional (si se usa, labelKey se usa para aria-label)
   */
  children?: React.ReactNode;
}

/**
 * Button - Componente wrapper de botón con i18n y testId obligatorios
 * 
 * Reglas:
 * - testId es obligatorio
 * - Todo texto visible usa t() con fallback
 * - Separación CSS/JSX/JS
 */
export const Button: React.FC<ButtonProps> = ({
  testId,
  label,
  labelKey,
  variant = 'primary',
  size = 'medium',
  fullWidth = false,
  children,
  className,
  disabled,
  ...props
}) => {
  // Determinar el texto visible
  const buttonText = children 
    ? children 
    : labelKey 
      ? t(labelKey, label || '')
      : label || '';

  // aria-label para accesibilidad (usar labelKey si existe, sino label)
  const ariaLabel = labelKey 
    ? t(labelKey, label || buttonText?.toString() || '')
    : label || buttonText?.toString() || '';

  // Clases CSS
  const buttonClasses = [
    styles.button,
    styles[variant],
    styles[size],
    fullWidth && styles.fullWidth,
    disabled && styles.disabled,
    className
  ].filter(Boolean).join(' ');

  return (
    <button
      data-testid={testId}
      className={buttonClasses}
      disabled={disabled}
      aria-label={ariaLabel}
      aria-disabled={disabled}
      {...props}
    >
      {buttonText}
    </button>
  );
};

