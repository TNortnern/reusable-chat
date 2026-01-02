export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: true },

  modules: [
    '@nuxt/ui',
    '@pinia/nuxt',
  ],

  runtimeConfig: {
    public: {
      apiUrl: process.env.NUXT_PUBLIC_API_URL || 'http://localhost:8000',
      reverbHost: process.env.NUXT_PUBLIC_REVERB_HOST || 'localhost',
      reverbPort: process.env.NUXT_PUBLIC_REVERB_PORT || '8080',
      reverbKey: process.env.NUXT_PUBLIC_REVERB_KEY || 'chat-app-key',
      demoApiKey: process.env.NUXT_PUBLIC_DEMO_API_KEY || '',
    }
  },

  css: ['~/assets/css/main.css'],
})
