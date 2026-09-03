'use strict';

/**
 * Global Dark Mode Manager for FamilyPlatform
 * Persists user preference to localStorage and listens for system preference changes.
 */
export function initDarkMode() {
  const toggleBtn = document.getElementById('darkModeToggle');
  const savedTheme = localStorage.getItem('familyPlatform_theme');
  const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  const isDark = savedTheme === 'dark' || (savedTheme === null && prefersDark);

  const themeBadge = document.getElementById('themeStatusBadge');

  const applyTheme = (dark) => {
    if (dark) {
      document.body.classList.add('dark-mode');
      if (themeBadge) {
        themeBadge.textContent = 'Dark';
        themeBadge.className = 'badge bg-warning text-dark';
      }
    } else {
      document.body.classList.remove('dark-mode');
      if (themeBadge) {
        themeBadge.textContent = 'Light';
        themeBadge.className = 'badge bg-secondary-subtle text-secondary';
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
