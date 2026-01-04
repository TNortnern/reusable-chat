import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
  build: {
    lib: {
      entry: resolve(__dirname, 'src/index.ts'),
      name: 'ReusableChat',
      fileName: 'widget',
      formats: ['iife'], // Single file for browser
    },
    rollupOptions: {
      output: {
        // Ensure everything is in one file
        inlineDynamicImports: true,
      },
    },
    minify: 'terser',
    sourcemap: true,
  },
  define: {
    'process.env.NODE_ENV': JSON.stringify('production'),
  },
})
