import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import { fileURLToPath, URL } from 'node:url'
import { visualizer } from 'rollup-plugin-visualizer'
import AutoImport from 'unplugin-auto-import/vite'
import Component from 'unplugin-vue-components/vite'
import { VueRouterAutoImports } from 'unplugin-vue-router'
import VueRouter from 'unplugin-vue-router/vite'
import { defineConfig } from 'vite'
import vueDevTools from 'vite-plugin-vue-devtools'
import Layouts from 'vite-plugin-vue-layouts'

const RouteGenerateExclude = ['**/components/**', '**/layouts/**', '**/data/**', '**/types/**']

export default defineConfig({
  plugins: [
    VueRouter({
      exclude: RouteGenerateExclude,
      dts: 'src/types/typed-router.d.ts',
    }),
    vue(),
    vueJsx(),
    vueDevTools(),
    tailwindcss(),
    visualizer({ gzipSize: true, brotliSize: true }),
    Layouts({
      defaultLayout: 'default',
    }),
    AutoImport({
      include: [
        /\.[tj]sx?$/,
        /\.vue$/,
        /\.md$/,
      ],
      imports: [
        'vue',
        VueRouterAutoImports,
      ],
      dirs: [
        'src/composables/**/*.ts',
        'src/constants/**/*.ts',
        'src/stores/**/*.ts',
      ],
      defaultExportByFilename: true,
      dts: 'src/types/auto-import.d.ts',
    }),
    Component({
      dirs: [
        'src/components',
      ],
      collapseSamePrefixes: true,
      directoryAsNamespace: true,
      dts: 'src/types/auto-import-components.d.ts',
    }),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  esbuild: {
    drop: ['debugger'],
    pure: ['console.log'],
  },
  server: {
    proxy: {
      // Proxy backend routes to http://127.0.0.1:8000
      // This allows cookies to work properly (same origin: localhost:5173)
      '/api': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        secure: false,
      },
      '/sanctum': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        secure: false,
      },
      '/login': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        secure: false,
      },
      '/logout': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        secure: false,
      },
      '/register': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        secure: false,
      },
    },
  },
})
