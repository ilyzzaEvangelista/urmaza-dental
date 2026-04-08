// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  ssr: false, // SPA style
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  runtimeConfig: {
    public: {
      /**
       * Laravel API origin. Empty string = same origin (see `nitro.devProxy` in dev).
       * Set `NUXT_PUBLIC_API_BASE=http://127.0.0.1:8000` to call Laravel directly.
       */
      apiBase: process.env.NUXT_PUBLIC_API_BASE ?? '',
      /** Same IANA zone as Laravel `APP_TIMEZONE` (appointment wall times) */
      clinicTimezone: process.env.NUXT_PUBLIC_CLINIC_TIMEZONE || 'Asia/Manila',
    },
  },

  /** Proxy API (and storage) to Laravel so the browser uses one origin (avoids CORS). */
  nitro: {
    devProxy: {
      '/api': { target: 'http://127.0.0.1:8000', changeOrigin: true },
      '/storage': { target: 'http://127.0.0.1:8000', changeOrigin: true },
    },
  },

  modules: [
    'vuetify-nuxt-module',
    '@pinia/nuxt',
    '@pinia-plugin-persistedstate/nuxt',
    '@nuxtjs/i18n',
    '@nuxtjs/color-mode',
    '@nuxt/icon',
    '@nuxt/image',
    '@nuxt/eslint'
  ],

  css: [
    '~/assets/scss/main.scss'
  ],

  i18n: {
    locales: ['en', 'tl'],
    defaultLocale: 'en',
    vueI18n: './i18n.config.ts'
  },

  colorMode: {
    preference: 'light',
    fallback: 'light'
  },

  vuetify: {
    moduleOptions: {
      /* vuetify options */
    }
  },

  vite: {
    optimizeDeps: {
      include: [
        '@vue/devtools-core',
        '@vue/devtools-kit'
      ]
    }
  }
})
