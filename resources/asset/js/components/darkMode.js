'use strict';

/**
 * Global Dark Mode Manager for FamilyPlatform
 * Persists user preference to localStorage and listens for system preference changes.
 */
export function initDarkMode() {
  const isPublicSurface = document.body?.classList.contains('public-layout') ||
                          document.body?.getAttribute('data-surface') === 'light' ||
                          document.querySelector('meta[name="page-scope"]')?.getAttribute('content') === 'public';

  const toggleBtn = document.getElementById('darkModeToggle');
  const savedTheme = localStorage.getItem('familyPlatform_theme');
  const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  
  // On public landing & auth pages, default to light mode unless explicitly saved by the user
  const isDark = isPublicSurface ? (savedTheme === 'dark') : (savedTheme === 'dark' || (savedTheme === null && prefersDark));

  const themeBadge = document.getElementById('themeStatusBadge');

  const applyTheme = (dark) => {
    if (dark) {
      document.body.classList.add('dark-mode');
      document.documentElement.classList.add('dark', 'dark-mode');
      if (themeBadge) {
        themeBadge.textContent = 'Dark';
        themeBadge.className = 'badge bg-warning text-dark';
      }
    } else {
      document.body.classList.remove('dark-mode', 'dark-theme');
      document.documentElement.classList.remove('dark', 'dark-mode');
      if (themeBadge) {
        themeBadge.textContent = 'Light';
        themeBadge.className = 'badge bg-secondary-subtle text-secondary';
      }
    }
    if (window.Alpine && typeof window.Alpine.store === 'function') {
      const themeStore = window.Alpine.store('theme');
      if (themeStore && themeStore.isDark !== dark) {
        themeStore.isDark = dark;
      }
    }
  };

  // Apply on initial load
  applyTheme(isDark);

  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      const nowDark = !document.body.classList.contains('dark-mode');
      applyTheme(nowDark);
      localStorage.setItem('familyPlatform_theme', nowDark ? 'dark' : 'light');
    });
  }
}
