/**
 * UI Layer Wrappers - Componentes reutilizables
 * 
 * Regla: Las features solo importan desde este archivo.
 * Nadie importa librerías UI externas directamente en features.
 */

export { Button } from './Button';
export type { ButtonProps } from './Button';

export { TextField } from './TextField';
export type { TextFieldProps } from './TextField';

export { DataTable } from './DataTable';
export type { DataTableProps, Column } from './DataTable';

export { DataGridDX } from './DataGridDX';
export type { DataGridDXProps } from './DataGridDX';

export { Modal } from './Modal';
export type { ModalProps } from './Modal';

