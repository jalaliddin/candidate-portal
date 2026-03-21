<template>
  <v-app-bar
    flat
    color="white"
    border="b"
    height="64"
    class="app-topbar"
  >
    <v-app-bar-nav-icon v-if="isMobile" @click="$emit('toggle-drawer')" />
    <v-app-bar-title>
      <span class="text-h6 font-weight-bold">{{ title }}</span>
    </v-app-bar-title>
    <template #append>
      <slot name="append" />
      <v-breadcrumbs
        v-if="breadcrumbs && breadcrumbs.length"
        :items="breadcrumbs"
        class="pa-0 mr-2 d-none d-sm-flex"
        density="compact"
      />
      <v-btn
        v-if="authStore.isAdmin"
        icon="mdi-bell-outline"
        variant="text"
        size="small"
        color="grey-darken-1"
      />
      <div class="user-avatar-sm ml-2 mr-3">{{ userInitials }}</div>
    </template>
  </v-app-bar>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useDisplay } from 'vuetify'

const props = defineProps({
  title: { type: String, default: '' },
  breadcrumbs: { type: Array, default: () => [] }
})

defineEmits(['toggle-drawer'])

const authStore = useAuthStore()
const { mobile } = useDisplay()
const isMobile = computed(() => mobile.value)

const userInitials = computed(() => {
  const name = authStore.user?.name || ''
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})
</script>

<style scoped>
.user-avatar-sm {
  width: 32px; height: 32px;
  border-radius: 50%;
  background: #1565C0;
  color: white;
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; font-weight: 600;
}
</style>
