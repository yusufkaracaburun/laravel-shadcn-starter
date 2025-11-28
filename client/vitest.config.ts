import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import { fileURLToPath, URL } from 'node:url'
import AutoImport from 'unplugin-auto-import/vite'
import Component from 'unplugin-vue-components/vite'
import { defineConfig } from 'vitest/config'

export default defineConfig({
  plugins: [
    vue(),
    vueJsx(),
    tailwindcss(),
    AutoImport({
      include: [
        /\.[tj]sx?$/,
        /\.vue$/,
      ],
      imports: [
        'vue',
        'vue-router',
      ],
      dirs: [
        'src/composables/**/*.ts',
        'src/stores/**/*.ts',
      ],
      defaultExportByFilename: true,
    }),
    Component({
      dirs: [
        'src/components',
      ],
      collapseSamePrefixes: true,
      directoryAsNamespace: true,
    }),
  ],
  test: {
    globals: true,
    environment: 'happy-dom',
    setupFiles: ['./tests/setup.ts'],
    include: ['tests/**/*.{test,spec}.{js,mjs,cjs,ts,mts,cts,jsx,tsx}'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      exclude: [
        'node_modules/',
        'tests/',
        '**/*.d.ts',
        '**/*.config.*',
        '**/dist/',
        '**/.{idea,git,cache,output,temp}/',
        '**/{karma,rollup,webpack,vite,vitest,jest,ava,babel,nyc,cypress,tsup,build,eslint,prettier}.config.*',
        '**/coverage/',
        '**/public/',
        '**/assets/',
        '**/types/',
        '**/*.vue.d.ts',
        '**/auto-import*.d.ts',
        '**/typed-router.d.ts',
      ],
    },
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
})

