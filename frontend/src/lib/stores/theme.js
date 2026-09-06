import { writable } from 'svelte/store';
import { browser } from '$app/environment';

function createThemeStore() {
  const { subscribe, set, update } = writable(false);

  function applyTheme(isDark) {
    if (browser) {
      if (isDark) {
        document.body.classList.add('dark-theme');
        localStorage.setItem('kerjain-theme', 'dark');
      } else {
        document.body.classList.remove('dark-theme');
        localStorage.setItem('kerjain-theme', 'light');
      }
    }
  }

  return {
    subscribe,
    init: () => {
      if (browser) {
        const savedTheme = localStorage.getItem('kerjain-theme');
        const isDark = savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches);
        set(isDark);
        applyTheme(isDark);
      }
    },
    toggle: () => update(isDark => {
      const newValue = !isDark;
      set(newValue);
      applyTheme(newValue);
      return newValue;
    })
  };
}

export const theme = createThemeStore();