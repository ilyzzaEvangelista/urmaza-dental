// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  ssr: false, // SPA style
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  runtimeConfig: {
    public: {
      /**
       * Laravel API origin. Empty = in **production** same origin as the SPA; in **`nuxt dev`** the app
       * resolves to `http://127.0.0.1:8000` (see `utils/apiBase.js`) unless you set this explicitly.
       */
      apiBase: process.env.NUXT_PUBLIC_API_BASE ?? '',
      /** Same IANA zone as Laravel `APP_TIMEZONE` (appointment wall times) */
      clinicTimezone: process.env.NUXT_PUBLIC_CLINIC_TIMEZONE || 'Asia/Manila',
      /** Clinic Facebook page — set `NUXT_PUBLIC_FACEBOOK_URL` in `.env` */
      facebookUrl: process.env.NUXT_PUBLIC_FACEBOOK_URL || 'https://www.facebook.com/',
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
    },
    /**
     * Browser `$fetch('/api/...')` in dev goes through Vite; without this, Nuxt returns 404 for `/api/*`.
     * `nitro.devProxy` alone does not always apply to client-side requests.
     */
    server: {
      proxy: {
        '/api': { target: 'http://127.0.0.1:8000', changeOrigin: true },
        '/storage': { target: 'http://127.0.0.1:8000', changeOrigin: true },
      },
    },
  }
})
