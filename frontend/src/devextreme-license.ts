/**
 * Configuración de licencia DevExtreme.
 * En CI o sin clave: usa modo trial (string vacío).
 * En desarrollo: definir VITE_DEVEXTREME_LICENSE en .env con tu clave.
 * Obtener clave en: https://www.devexpress.com/ClientCenter/DownloadManager/
 */
export const licenseKey = import.meta.env.VITE_DEVEXTREME_LICENSE ?? '';
