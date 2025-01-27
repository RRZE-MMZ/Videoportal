import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [laravel(['resources/css/app.css', 'resources/js/app.js'])],
  build: { chunkSizeWarningLimit: 1600, target: 'esnext' },
  resolve: {
    alias: {
      '@': '/resources/js',
      boot: './bootstrap',
    },
  },
  server: {
    host: '127.0.0.1',
    port: 5173,
    cors: true,
    watch: {
      ignored: ['**/storage/app/videos/**'],
    },
  },
  esbuild: {
    logOverride: { 'vite:css': 'silent' }, // Suppresses only 'vite:css' warnings
  },
});
