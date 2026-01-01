import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
  build: {
    lib: {
      entry: resolve(__dirname, 'src/widget.ts'),
      name: 'ChatWidget',
      fileName: 'widget',
      formats: ['iife']
    },
    outDir: 'dist',
    minify: 'terser',
  },
})
