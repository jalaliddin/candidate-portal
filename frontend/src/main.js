import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import vuetify from './plugins/vuetify'
import i18n from './i18n'
import './assets/global.css'
import '@mdi/font/css/materialdesignicons.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(vuetify)
app.use(i18n)

app.config.globalProperties.$storageUrl = import.meta.env.VITE_STORAGE_URL || 'http://localhost:8000/storage'

import { useAuthStore } from './stores/auth'
const authStore = useAuthStore()
authStore.restoreSession()

app.mount('#app')
