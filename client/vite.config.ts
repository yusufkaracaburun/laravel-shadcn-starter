import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import process from 'node:process'
import { fileURLToPath, URL } from 'node:url'
import { visualizer } from 'rollup-plugin-visualizer'
import AutoImport from 'unplugin-auto-import/vite'
import Component from 'unplugin-vue-components/vite'
import { VueRouterAutoImports } from 'unplugin-vue-router'
import VueRouter from 'unplugin-vue-router/vite'
import { defineConfig, loadEnv } from 'vite'
import vueDevTools from 'vite-plugin-vue-devtools'
import Layouts from 'vite-plugin-vue-layouts'

const RouteGenerateExclude = ['**/components/**', '**/layouts/**', '**/data/**', '**/types/**']

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const apiUrl = env.VITE_SERVER_API_URL

  return {
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
        include: [/\.[tj]sx?$/, /\.vue$/, /\.md$/],
        imports: ['vue', VueRouterAutoImports],
        dirs: ['src/composables/**/*.ts', 'src/constants/**/*.ts', 'src/stores/**/*.ts'],
        defaultExportByFilename: true,
        dts: 'src/types/auto-import.d.ts',
      }),
      Component({
        dirs: ['src/components'],
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
    build: {
      manifest: true,
    },
    server: {
      proxy: {
        '/api': {
          target: apiUrl,
          changeOrigin: true,
          secure: false,
        },
        '/sanctum': {
          target: apiUrl,
          changeOrigin: true,
          secure: false,
        },
        '/login': {
          target: apiUrl,
          changeOrigin: true,
          secure: false,
        },
        '/register': {
          target: apiUrl,
          changeOrigin: true,
          secure: false,
        },
        '/logout': {
          target: apiUrl,
          changeOrigin: true,
          secure: false,
        },
      },
    },
  }
})
