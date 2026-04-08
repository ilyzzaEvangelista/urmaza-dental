// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  ssr: false, // SPA style
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  modules: [
    '@nuxtjs/tailwindcss',
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
    preference: 'system',
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
