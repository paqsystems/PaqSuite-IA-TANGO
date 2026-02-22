/**
 * Utilidades para manejo de duración
 * 
 * Funciones helper para convertir entre formato horario (hh:mm) y minutos.
 * 
 * Regla de formato:
 * - Formato Visualización (Frontend): hh:mm (ej: "02:30" = 150 minutos)
 * - Formato Interno (API/DB): minutos (número entero)
 * 
 * @see TR-028(MH)-carga-de-tarea-diaria.md
 */

/**
 * Convierte minutos a formato horario hh:mm
 * 
 * @param minutos Número de minutos (entero)
 * @returns String en formato hh:mm (ej: "02:30")
 */
export function minutesToTime(minutos: number): string {
  if (isNaN(minutos) || minutos < 0) {
    return '';
  }
  
  const hours = Math.floor(minutos / 60);
  const minutes = minutos % 60;
  
  return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
}

/**
 * Convierte formato horario hh:mm a minutos
 * 
 * @param timeString String en formato hh:mm (ej: "02:30")
 * @returns Número de minutos o null si el formato es inválido
 */
export function timeToMinutes(timeString: string): number | null {
  if (!timeString || !timeString.trim()) {
    return null;
  }
  
  // Validar formato hh:mm
  const timeRegex = /^(\d{1,2}):(\d{2})$/;
  const match = timeString.trim().match(timeRegex);
  
  if (!match) {
    return null;
  }
  
  const hours = parseInt(match[1], 10);
  const minutes = parseInt(match[2], 10);
  
  // Validar rangos
  if (isNaN(hours) || isNaN(minutes) || hours < 0 || minutes < 0 || minutes >= 60) {
    return null;
  }
  
  // Máximo 24 horas
  if (hours > 24 || (hours === 24 && minutes > 0)) {
    return null;
  }
  
  return hours * 60 + minutes;
}

/**
 * Valida si un string tiene formato hh:mm válido
 * 
 * @param timeString String a validar
 * @returns true si el formato es válido
 */
export function isValidTimeFormat(timeString: string): boolean {
  return timeToMinutes(timeString) !== null;
}

/**
 * Formatea un valor de minutos para mostrar en input de tiempo
 * Si el valor es múltiplo de 15, lo formatea como hh:mm
 * 
 * @param minutos Número de minutos o string vacío
 * @returns String en formato hh:mm o string vacío
 */
export function formatMinutesForInput(minutos: number | string): string {
  if (minutos === '' || minutos === null || minutos === undefined) {
    return '';
  }
  
  const numMinutos = typeof minutos === 'string' ? parseInt(minutos, 10) : minutos;
  
  if (isNaN(numMinutos) || numMinutos < 0) {
    return '';
  }
  
  return minutesToTime(numMinutos);
}
