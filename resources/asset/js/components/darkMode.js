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

  const applyTheme = (dark) => {
    if (dark) {
      document.body.classList.add('dark-mode');
      if (toggleBtn) {
        toggleBtn.innerHTML = '<i class="bi bi-sun-fill"></i>';
        toggleBtn.setAttribute('title', 'Switch to Light Mode');
        toggleBtn.setAttribute('aria-label', 'Switch to Light Mode');
      }
    } else {
      document.body.classList.remove('dark-mode');
      if (toggleBtn) {
        toggleBtn.innerHTML = '<i class="bi bi-moon-fill"></i>';
        toggleBtn.setAttribute('title', 'Switch to Dark Mode');
        toggleBtn.setAttribute('aria-label', 'Switch to Dark Mode');
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
