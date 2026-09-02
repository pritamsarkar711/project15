import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import path from 'path';
import {defineConfig} from 'vite';

/**
 * Two build modes:
 *
 * 1. Default (AI Studio preview): bundles the root index.html React app
 *    into dist/, exactly as before.
 *
 * 2. Laravel asset build (`LARAVEL_BUILD=1 bun run build:laravel`): bundles
 *    the Blade app's entries (resources/css/app.css + resources/js/app.js)
 *    into public/build/ with a Vite manifest. Laravel's Vite integration
 *    and the ViteAssets fallback both resolve those hashed files, and the
 *    committed public/build is what Hostinger serves (no npm on the server).
 */
export default defineConfig(() => {
  const laravel = process.env.LARAVEL_BUILD === '1';

  return {
    plugins: [react(), tailwindcss()],
    resolve: {
      alias: {
        '@': path.resolve(__dirname, '.'),
      },
    },
    server: {
      // HMR is disabled in AI Studio via DISABLE_HMR env var.
      // Do not modify—file watching is disabled to prevent flickering during agent edits.
      hmr: process.env.DISABLE_HMR !== 'true',
      // Disable file watching when DISABLE_HMR is true to save CPU during agent edits.
      watch: process.env.DISABLE_HMR === 'true' ? null : {},
    },
    ...(laravel
      ? {
          base: '/',
          // public/ must NOT be copied into public/build: outDir lives inside
          // public, and Laravel serves public/ directly already.
          publicDir: false,
          build: {
            outDir: 'public/build',
            emptyOutDir: true,
            manifest: true,
            assetsDir: 'assets',
            rollupOptions: {
              input: [
                path.resolve(__dirname, 'resources/css/app.css'),
                path.resolve(__dirname, 'resources/js/app.js'),
              ],
            },
          },
        }
      : {}),
  };
});
