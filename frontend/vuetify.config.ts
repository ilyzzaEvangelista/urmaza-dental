import { defineVuetifyConfiguration } from 'vuetify-nuxt-module/custom-configuration'
import { en } from 'vuetify/locale'

export default defineVuetifyConfiguration({
  locale: {
    locale: 'en',
    fallback: 'en',
    messages: { en },
  },
})
