import React from 'react';
import { t } from '../../i18n';
import styles from './TextField.module.css';

export interface TextFieldProps extends Omit<React.InputHTMLAttributes<HTMLInputElement>, 'size'> {
  /**
   * Test ID obligatorio para testing E2E
   */
  testId: string;
  /**
   * Label del campo (usará t() internamente)
   */
  label?: string;
  /**
   * Key de traducción para el label
   */
  labelKey?: string;
  /**
   * Mensaje de error (usará t() internamente)
   */
  error?: string;
  /**
   * Key de traducción para el mensaje de error
   */
  errorKey?: string;
  /**
   * Texto de ayuda/placeholder (usará t() internamente)
   */
  helperText?: string;
  /**
   * Key de traducción para el helper text
   */
  helperTextKey?: string;
  /**
   * Si el campo es requerido
   */
  required?: boolean;
  /**
   * Tamaño del campo
   */
  size?: 'small' | 'medium' | 'large';
  /**
   * Si es true, el campo ocupa todo el ancho disponible
   */
  fullWidth?: boolean;
}

/**
 * TextField - Componente wrapper de input con i18n y testId obligatorios
 * 
 * Reglas:
 * - testId es obligatorio
 * - Todo texto visible usa t() con fallback
 * - Separación CSS/JSX/JS
 * - Accesibilidad completa (labels, aria-required, aria-invalid, aria-describedby)
 */
export const TextField: React.FC<TextFieldProps> = ({
  testId,
  label,
  labelKey,
  error,
  errorKey,
  helperText,
  helperTextKey,
  required = false,
  size = 'medium',
  fullWidth = false,
  id,
  className,
  disabled,
  ...props
}) => {
  // Generar ID único si no se proporciona
  const fieldId = id || `textfield-${testId}`;
  const errorId = error || errorKey ? `${fieldId}-error` : undefined;
  const helperId = helperText || helperTextKey ? `${fieldId}-helper` : undefined;
  const describedBy = [errorId, helperId].filter(Boolean).join(' ') || undefined;

  // Textos traducidos
  const labelText = labelKey ? t(labelKey, label || '') : label || '';
  const errorText = errorKey ? t(errorKey, error || '') : error || '';
  const helperTextTranslated = helperTextKey ? t(helperTextKey, helperText || '') : helperText || '';

  // Clases CSS
  const inputClasses = [
    styles.input,
    styles[size],
    fullWidth && styles.fullWidth,
    errorText && styles.error,
    disabled && styles.disabled,
    className
  ].filter(Boolean).join(' ');

  return (
    <div className={styles.container}>
      {labelText && (
        <label htmlFor={fieldId} className={styles.label}>
          {labelText}
          {required && <span className={styles.required} aria-label={t('common.required', 'requerido')}> *</span>}
        </label>
      )}
      <input
        id={fieldId}
        data-testid={testId}
        className={inputClasses}
        disabled={disabled}
        required={required}
        aria-required={required}
        aria-invalid={!!errorText}
        aria-describedby={describedBy}
        aria-label={labelText || undefined}
        {...props}
      />
      {errorText && (
        <div
          id={errorId}
          data-testid={`${testId}-error`}
          className={styles.errorMessage}
          role="alert"
          aria-live="polite"
        >
          {errorText}
        </div>
      )}
      {helperTextTranslated && !errorText && (
        <div
          id={helperId}
          data-testid={`${testId}-helper`}
          className={styles.helperText}
        >
          {helperTextTranslated}
        </div>
      )}
    </div>
  );
};

