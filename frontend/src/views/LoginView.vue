<template>
  <v-main style="background: #F0F4F8; min-height: 100vh;">
    <v-container class="d-flex align-center justify-center" style="min-height: 100vh;">
      <v-card max-width="420" width="100%" style="border-radius: 16px;">
        <v-card-text class="pa-8">
          <!-- Logo -->
          <div class="text-center mb-6">
            <v-icon
              icon="mdi-briefcase-account"
              color="primary"
              size="72"
              class="mb-3"
            />
            <div class="text-h5 font-weight-bold mb-1">Candidate Portal</div>
            <div class="text-body-2 text-medium-emphasis">{{ $t('auth.welcome') }}</div>
          </div>

          <!-- Error alert -->
          <v-alert
            v-if="errorMsg"
            type="error"
            variant="tonal"
            closable
            class="mb-4"
            @click:close="errorMsg = ''"
          >
            {{ errorMsg }}
          </v-alert>

          <!-- Form -->
          <v-form @submit.prevent="handleLogin">
            <v-text-field
              v-model="email"
              :label="$t('auth.email')"
              type="email"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="mdi-email-outline"
              class="mb-3"
              hide-details="auto"
              :rules="[v => !!v || 'Required', v => /.+@.+/.test(v) || 'Invalid email']"
            />

            <v-text-field
              v-model="password"
              :label="$t('auth.password')"
              :type="showPassword ? 'text' : 'password'"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="mdi-lock-outline"
              :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
              class="mb-5"
              hide-details
              @click:append-inner="showPassword = !showPassword"
            />

            <v-btn
              type="submit"
              color="primary"
              variant="flat"
              size="large"
              block
              :loading="loading"
            >
              {{ $t('auth.loginBtn') }}
            </v-btn>
          </v-form>

          <!-- Language toggle -->
          <div class="d-flex justify-center ga-2 mt-5">
            <v-btn
              size="x-small"
              :variant="currentLang === 'en' ? 'flat' : 'outlined'"
              :color="currentLang === 'en' ? 'primary' : undefined"
              @click="setLang('en')"
            >EN</v-btn>
            <v-btn
              size="x-small"
              :variant="currentLang === 'de' ? 'flat' : 'outlined'"
              :color="currentLang === 'de' ? 'primary' : undefined"
              @click="setLang('de')"
            >DE</v-btn>
          </div>
        </v-card-text>
      </v-card>
    </v-container>
  </v-main>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const { locale } = useI18n()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const errorMsg = ref('')

const currentLang = computed(() => locale.value)

async function handleLogin() {
  if (!email.value || !password.value) return
  loading.value = true
  errorMsg.value = ''
  try {
    await authStore.login(email.value, password.value)
    if (authStore.isAdmin) {
      router.push('/admin/dashboard')
    } else {
      router.push('/candidates')
    }
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Login failed'
  } finally {
    loading.value = false
  }
}

function setLang(lang) {
  locale.value = lang
  localStorage.setItem('lang', lang)
}
</script>
