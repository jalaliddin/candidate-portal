<template>
  <v-layout>
    <AppSidebar v-model="drawer" />
    <v-main>
      <AppTopBar :title="$t('reports.title')" @toggle-drawer="drawer = !drawer" />

      <v-container fluid class="pa-6">
        <v-tabs
          v-model="activeTab"
          color="primary"
          class="mb-6 reports-tabs"
          bg-color="white"
          style="border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08)"
        >
          <v-tab to="/reports/candidates" value="/reports/candidates">
            <v-icon start>mdi-account-group</v-icon>
            {{ $t('reports.candidateList') }}
          </v-tab>
          <v-tab to="/reports/requests-stats" value="/reports/requests-stats">
            <v-icon start>mdi-chart-bar</v-icon>
            {{ $t('reports.requestsStats') }}
          </v-tab>
          <v-tab to="/reports/candidate-profile" value="/reports/candidate-profile">
            <v-icon start>mdi-account-details</v-icon>
            {{ $t('reports.candidateProfile') }}
          </v-tab>
        </v-tabs>

        <router-view />
      </v-container>
    </v-main>
  </v-layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppTopBar from '@/components/layout/AppTopBar.vue'

const drawer = ref(false)
const route = useRoute()
const activeTab = computed(() => route.path)
</script>
