'use strict';

import { getPlatformStore } from '../apps/shared/platformStore';

/**
 * Global Dark Mode Manager for FamilyPlatform
 * Synchronises theme state through the unified platformStore (Alpine $store.theme <-> Preact).
 */
export function initDarkMode() {
  const isPublicSurface = document.body?.classList.contains('public-layout') ||
                          document.body?.getAttribute('data-surface') === 'light' ||
                          document.querySelector('meta[name="page-scope"]')?.getAttribute('content') === 'public';

  const savedTheme = localStorage.getItem('familyPlatform_theme');
  const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

  // On public landing & auth pages (data-surface="light" / .public-layout), always enforce light mode
  const isDark = isPublicSurface ? false : (savedTheme === 'dark' || (savedTheme === null && prefersDark));

  const platformStore = getPlatformStore();
  platformStore.theme.setDark(isDark, { persist: false, emit: false });

  const toggleBtn = document.getElementById('darkModeToggle');
  if (toggleBtn && !toggleBtn.hasAttribute('x-data')) {
    toggleBtn.addEventListener('click', () => {
      platformStore.theme.toggle();
    });
  }
}
