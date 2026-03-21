<template>
  <v-layout>
    <AppSidebar v-model="drawer" />
    <v-main>
      <router-view @toggle-drawer="drawer = !drawer" />
    </v-main>
  </v-layout>
</template>

<script setup>
import { ref } from 'vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
const drawer = ref(false)
</script>
