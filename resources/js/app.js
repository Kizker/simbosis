import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

createInertiaApp({
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el);
  },
});

/* PWA: register SW (non-blocking) */
if ('serviceWorker' in navigator) {
  const SW_VERSION = 'v6';
  const SW_VERSION_KEY = 'simbiosis_sw_version';

  window.addEventListener('load', () => {
    // One-time cache flush whenever SW version changes.
    if (localStorage.getItem(SW_VERSION_KEY) !== SW_VERSION && 'caches' in window) {
      caches.keys().then((keys) => Promise.all(keys.map((k) => caches.delete(k)))).catch(() => {});
      localStorage.setItem(SW_VERSION_KEY, SW_VERSION);
    }

    navigator.serviceWorker
      .register(`/sw.js?v=${SW_VERSION}`, { updateViaCache: 'none' })
      .then((reg) => reg.update())
      .catch(() => {});
  });
}
