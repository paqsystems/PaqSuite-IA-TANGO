/**
 * LanguageSelector - Selector de idioma para i18n
 */

import React from 'react';
import { useTranslation } from 'react-i18next';

const languages = [
  { code: 'es', label: 'Español' },
  { code: 'en', label: 'English' }
];

export function LanguageSelector(): React.ReactElement {
  const { i18n } = useTranslation();

  return (
    <select
      value={i18n.language}
      onChange={(e) => i18n.changeLanguage(e.target.value)}
      data-testid="language-selector"
      aria-label="Seleccionar idioma"
      className="language-selector"
    >
      {languages.map((lang) => (
        <option key={lang.code} value={lang.code}>
          {lang.label}
        </option>
      ))}
    </select>
  );
}
