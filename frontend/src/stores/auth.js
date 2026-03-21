import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/plugins/axios'
import i18n from '@/i18n'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')

  async function login(email, password) {
    const response = await api.post('/login', { email, password })
    token.value = response.data.token
    user.value = response.data.user
    localStorage.setItem('token', response.data.token)
    localStorage.setItem('user', JSON.stringify(response.data.user))
    if (user.value.preferred_language) {
      i18n.global.locale.value = user.value.preferred_language
      localStorage.setItem('lang', user.value.preferred_language)
    }
    return response.data
  }

  async function logout() {
    try {
      await api.post('/logout')
    } catch {}
    token.value = null
    user.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    // Import router lazily to avoid circular dependency
    const { default: router } = await import('@/router')
    router.push('/login')
  }

  function restoreSession() {
    const savedToken = localStorage.getItem('token')
    const savedUser = localStorage.getItem('user')
    if (savedToken && savedUser) {
      token.value = savedToken
      user.value = JSON.parse(savedUser)
      if (user.value.preferred_language) {
        i18n.global.locale.value = user.value.preferred_language
      }
    }
  }

  async function updateLanguage(lang) {
    try {
      await api.patch('/me/language', { preferred_language: lang })
    } catch {}
    if (user.value) {
      user.value.preferred_language = lang
      localStorage.setItem('user', JSON.stringify(user.value))
    }
    i18n.global.locale.value = lang
    localStorage.setItem('lang', lang)
  }

  return { user, token, isAuthenticated, isAdmin, login, logout, restoreSession, updateLanguage }
})
