import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import 'vuetify/styles'

export default createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        colors: {
          primary: '#1565C0',
          secondary: '#00897B',
          accent: '#FF6F00',
          background: '#F0F4F8',
          surface: '#FFFFFF',
          error: '#B00020',
          info: '#1565C0',
          success: '#2E7D32',
          warning: '#E65100',
        }
      }
    }
  }
})
