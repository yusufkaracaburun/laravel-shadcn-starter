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
      // Proxy al je Sanctum / API routes naar de backend
      '^/(sanctum|login|logout|register|api)': {
        target: process.env.VITE_SERVER_API_URL || 'https://api.skeleton:8890',
        changeOrigin: true,
        secure: false,
        ws: true,
        configure: (proxy, _options) => {
          proxy.on('proxyReq', (proxyReq, req, _res) => {
            if (req.headers.origin) {
              proxyReq.setHeader('Origin', req.headers.origin)
            }
            if (req.headers.referer) {
              proxyReq.setHeader('Referer', req.headers.referer)
            }
          })
          // Handle response cookies - rewrite domain and path
          proxy.on('proxyRes', (proxyRes, _req, _res) => {
            const setCookieHeaders = proxyRes.headers['set-cookie']
            if (setCookieHeaders) {
              proxyRes.headers['set-cookie'] = setCookieHeaders.map((cookie) => {
                // Remove domain attribute for localhost (browser will use current domain)
                let newCookie = cookie.replaceAll(/Domain=[^;]+;?/gi, '')
                // Ensure path is set to root
                if (cookie.includes('Path=')) {
                  newCookie = newCookie.replaceAll(/Path=[^;]+/gi, 'Path=/')
                }
                else {
                  newCookie = newCookie.replace(/;([^;]*)$/, '; Path=/; $1')
                }
                // Remove any double semicolons
                newCookie = newCookie.replaceAll(/;{2,}/g, ';')
                return newCookie
              })
            }
          })
        },
      },
    },
  },
  build: {
    manifest: true,
  },
})
