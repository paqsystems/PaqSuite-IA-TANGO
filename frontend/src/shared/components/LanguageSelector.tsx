/**
 * LanguageSelector - Selector de idioma para i18n
 * @see docs/04-tareas/000-Generalidades/TR-004-seleccion-idioma.md
 */

import React, { useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { getLocale, setLocale, isAuthenticated } from '../utils/tokenStorage';

const languages = [
  { code: 'es', label: 'Español' },
  { code: 'en', label: 'English' },
];

export function LanguageSelector(): React.ReactElement {
  const { i18n } = useTranslation();
  const currentLang = i18n.language?.startsWith('es') ? 'es' : i18n.language?.startsWith('en') ? 'en' : 'es';

  useEffect(() => {
    const stored = getLocale();
    if (stored && stored !== i18n.language) {
      i18n.changeLanguage(stored);
    }
  }, []);

  const handleChange = (code: string) => {
    i18n.changeLanguage(code);
    setLocale(code);
    if (isAuthenticated()) {
      import('../../features/user/services/preferences.service').then(({ updatePreferences }) =>
        updatePreferences({ locale: code })
      );
    }
  };

  return (
    <select
      value={currentLang}
      onChange={(e) => handleChange(e.target.value)}
      data-testid="languageSelector"
      aria-label="Seleccionar idioma"
      className="language-selector"
    >
      {languages.map((lang) => (
        <option key={lang.code} value={lang.code} data-testid={`languageSelector.option.${lang.code}`}>
          {lang.label}
        </option>
      ))}
    </select>
  );
}
