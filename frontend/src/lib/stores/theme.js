import { writable } from 'svelte/store';

function createThemeStore() {
  const { subscribe, set, update } = writable(false);

  return {
    subscribe,
    // Menjalankan pengecekan awal saat aplikasi dimuat
    init: () => {
      if (typeof window !== 'undefined') {
        // Cek jika +layout.svelte sudah memasang class dark-theme
        if (document.body.classList.contains('dark-theme')) {
          set(true);
          return;
        }

        // Fallback pengecekan via localStorage & preferensi sistem
        const savedTheme = localStorage.getItem('kerjain-theme');
        const isDark = savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches);
        
        set(isDark);
        
        if (isDark) {
          document.body.classList.add('dark-theme');
        } else {
          document.body.classList.remove('dark-theme');
        }
      }
    },
    // Fungsi untuk mengubah tema
    toggle: () => update(isDark => {
      const newValue = !isDark;
      if (typeof window !== 'undefined') {
        if (newValue) {
          document.body.classList.add('dark-theme');
          localStorage.setItem('kerjain-theme', 'dark');
        } else {
          document.body.classList.remove('dark-theme');
          localStorage.setItem('kerjain-theme', 'light');
        }
      }
      return newValue;
    })
  };
}

export const theme = createThemeStore();