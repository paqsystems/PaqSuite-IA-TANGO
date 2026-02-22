/**
 * Punto de entrada de la aplicación React
 * Incluye estilos y tema de DevExtreme, configuración i18n
 */

import './i18n';
import 'devextreme/dist/css/dx.light.css';
import React from 'react';
import ReactDOM from 'react-dom/client';
import { App } from './app/App';

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);
