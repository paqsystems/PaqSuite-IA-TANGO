/**
 * Utilidades para manejo de fechas
 * 
 * Funciones helper para formateo de visualización de fechas (DMY) y parsing (YMD).
 * 
 * Regla de formato:
 * - Formato Interno (Todo el sistema): YMD (YYYY-MM-DD)
 * - Formato Visualización (Frontend): DMY (DD/MM/YYYY) solo para mostrar al usuario
 * 
 * @see TR-028(MH)-carga-de-tarea-diaria.md
 */

/**
 * Parsea un string DMY (DD/MM/YYYY) a formato YMD (YYYY-MM-DD)
 * 
 * @param dmyString String en formato DD/MM/YYYY
 * @returns String en formato YYYY-MM-DD o null si el formato es inválido
 */
export function parseDMYtoYMD(dmyString: string): string | null {
  if (!dmyString || !dmyString.trim()) {
    return null;
  }
  
  // Validar formato DD/MM/YYYY
  const dmyRegex = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/;
  const match = dmyString.trim().match(dmyRegex);
  
  if (!match) {
    return null;
  }
  
  const day = parseInt(match[1], 10);
  const month = parseInt(match[2], 10);
  const year = parseInt(match[3], 10);
  
  // Validar rangos
  if (isNaN(day) || isNaN(month) || isNaN(year)) {
    return null;
  }
  
  if (day < 1 || day > 31 || month < 1 || month > 12 || year < 1900 || year > 2100) {
    return null;
  }
  
  // Crear fecha para validar que es válida (ej: no 31/02)
  const date = new Date(year, month - 1, day);
  if (date.getDate() !== day || date.getMonth() !== month - 1 || date.getFullYear() !== year) {
    return null;
  }
  
  return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
}

/**
 * Formatea una fecha a formato DMY (DD/MM/YYYY) para visualización
 * 
 * @param date Date, string YMD (YYYY-MM-DD) o string ISO
 * @returns String en formato DD/MM/YYYY
 */
export function formatDateDMY(date: Date | string): string {
  let dateObj: Date;
  
  if (typeof date === 'string') {
    // Si es string YMD (YYYY-MM-DD), parsearlo
    if (/^\d{4}-\d{2}-\d{2}$/.test(date)) {
      const [year, month, day] = date.split('-').map(Number);
      dateObj = new Date(year, month - 1, day);
    } else {
      // Intentar parsear como ISO
      dateObj = new Date(date);
    }
  } else {
    dateObj = date;
  }
  
  if (isNaN(dateObj.getTime())) {
    return '';
  }
  
  const day = String(dateObj.getDate()).padStart(2, '0');
  const month = String(dateObj.getMonth() + 1).padStart(2, '0');
  const year = dateObj.getFullYear();
  
  return `${day}/${month}/${year}`;
}

/**
 * Formatea una fecha a formato YMD (YYYY-MM-DD) para valores de input
 * 
 * @param date Date o string ISO
 * @returns String en formato YYYY-MM-DD
 */
export function formatDateYMD(date: Date | string): string {
  let dateObj: Date;
  
  if (typeof date === 'string') {
    dateObj = new Date(date);
  } else {
    dateObj = date;
  }
  
  if (isNaN(dateObj.getTime())) {
    return '';
  }
  
  const year = dateObj.getFullYear();
  const month = String(dateObj.getMonth() + 1).padStart(2, '0');
  const day = String(dateObj.getDate()).padStart(2, '0');
  
  return `${year}-${month}-${day}`;
}

/**
 * Parsea un string YMD (YYYY-MM-DD) a objeto Date
 * 
 * @param dateString String en formato YYYY-MM-DD
 * @returns Date o null si el formato es inválido
 */
export function parseDateYMD(dateString: string): Date | null {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
    return null;
  }
  
  const [year, month, day] = dateString.split('-').map(Number);
  const date = new Date(year, month - 1, day);
  
  if (isNaN(date.getTime())) {
    return null;
  }
  
  return date;
}

/**
 * Valida si un string tiene formato YMD válido (YYYY-MM-DD)
 * 
 * @param dateString String a validar
 * @returns true si el formato es válido
 */
export function isValidYMD(dateString: string): boolean {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
    return false;
  }
  
  const date = parseDateYMD(dateString);
  return date !== null;
}

/**
 * Obtiene la fecha actual en formato YMD (YYYY-MM-DD)
 * 
 * @returns String en formato YYYY-MM-DD
 */
export function getTodayYMD(): string {
  return formatDateYMD(new Date());
}

/**
 * Verifica si una fecha es futura
 * 
 * @param dateString String en formato YMD (YYYY-MM-DD)
 * @returns true si la fecha es futura
 */
export function isFutureDate(dateString: string): boolean {
  const date = parseDateYMD(dateString);
  if (!date) {
    return false;
  }
  
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  return date > today;
}
