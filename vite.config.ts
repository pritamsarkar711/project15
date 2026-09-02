import tailwindcss from '@tailwindcss/vite';
import path from 'path';
import {defineConfig} from 'vite';

/**
 * Laravel asset build.
 *
 * Bundles the Blade app's entries (resources/css/app.css + resources/js/app.js)
 * into public/build/ with a Vite manifest. Laravel's Vite integration and the
 * ViteAssets fallback both resolve those hashed files, and the committed
 * public/build is what Hostinger serves (no npm on the server).
 *
 * The legacy AI Studio React preview (root index.html + src/) was removed as
 * part of the repo cleanup; this config now has a single build mode. Build
 * with either `bun run build` or `LARAVEL_BUILD=1 bun run build:laravel`.
 */
export default defineConfig({
  plugins: [tailwindcss()],
  base: '/',
  // public/ must NOT be copied into public/build: outDir lives inside
  // public, and Laravel serves public/ directly already.
  publicDir: false,
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    // Laravel's ViteAssets helper reads public/build/manifest.json,
    // so write the manifest there instead of the .vite/ default.
    manifest: 'manifest.json',
    assetsDir: 'assets',
    rollupOptions: {
      input: [
        path.resolve(__dirname, 'resources/css/app.css'),
        path.resolve(__dirname, 'resources/js/app.js'),
      ],
    },
  },
});
