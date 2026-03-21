<template>
  <div>
    <AppTopBar :title="$t('admin.dashboard')" @toggle-drawer="$emit('toggle-drawer')" />

    <v-container fluid class="pa-6">
      <!-- Welcome banner -->
      <v-card elevation="2" class="mb-6" color="primary">
        <v-card-text class="pa-5">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-h6 font-weight-bold" style="color:white">
                {{ $t('admin.welcomeBack') }}, {{ authStore.user?.name }} 👋
              </div>
              <div style="color:rgba(255,255,255,0.8); font-size:14px">
                {{ today }}
              </div>
            </div>
            <v-icon icon="mdi-view-dashboard" size="48" style="color:rgba(255,255,255,0.3)" />
          </div>
        </v-card-text>
      </v-card>

      <!-- Stat Cards -->
      <v-row class="mb-4">
        <v-col cols="12" sm="6" lg="3">
          <StatCard
            :value="stats?.active_candidates || 0"
            :label="$t('admin.totalActive')"
            icon="mdi-account-group"
            icon-color="#1565C0"
          />
        </v-col>
        <v-col cols="12" sm="6" lg="3">
          <StatCard
            :value="stats?.pending_requests || 0"
            :label="$t('admin.pendingRequests')"
            icon="mdi-clock-outline"
            icon-color="#E65100"
          />
        </v-col>
        <v-col cols="12" sm="6" lg="3">
          <StatCard
            :value="stats?.active_clients || 0"
            :label="$t('admin.totalClients')"
            icon="mdi-account-tie"
            icon-color="#00897B"
          />
        </v-col>
        <v-col cols="12" sm="6" lg="3">
          <StatCard
            :value="stats?.new_requests_this_week || 0"
            :label="$t('admin.doneThisWeek')"
            icon="mdi-check-circle"
            icon-color="#2E7D32"
          />
        </v-col>
      </v-row>

      <!-- Charts -->
      <v-row class="mb-4">
        <v-col cols="12" md="6">
          <DonutChart
            :title="$t('admin.candidatesByStatus')"
            :items="statusChartItems"
            :colors="['#2E7D32', '#E65100', '#1565C0']"
          />
        </v-col>
        <v-col cols="12" md="6">
          <DonutChart
            :title="$t('admin.requestsByType')"
            :items="typeChartItems"
            :colors="['#00897B', '#1565C0', '#6A1B9A']"
          />
        </v-col>
      </v-row>

      <!-- Top Tables -->
      <v-row class="mb-4">
        <v-col cols="12" md="6">
          <v-card elevation="2">
            <v-card-title class="pa-4">{{ $t('admin.topNationalities') }}</v-card-title>
            <v-data-table
              :headers="nationalityHeaders"
              :items="stats?.top_nationalities || []"
              density="compact"
              :items-per-page="-1"
              hide-default-footer
            >
              <template #item.count="{ item }">
                <v-chip size="small" color="primary" variant="tonal">{{ item.count }}</v-chip>
              </template>
            </v-data-table>
          </v-card>
        </v-col>
        <v-col cols="12" md="6">
          <v-card elevation="2">
            <v-card-title class="pa-4">{{ $t('admin.topOccupations') }}</v-card-title>
            <v-data-table
              :headers="occupationHeaders"
              :items="stats?.top_occupations || []"
              density="compact"
              :items-per-page="-1"
              hide-default-footer
            >
              <template #item.count="{ item }">
                <v-chip size="small" color="secondary" variant="tonal">{{ item.count }}</v-chip>
              </template>
            </v-data-table>
          </v-card>
        </v-col>
      </v-row>

      <!-- Recent Requests -->
      <v-card elevation="2">
        <v-card-title class="pa-4">{{ $t('admin.recentRequests') }}</v-card-title>
        <v-data-table
          :headers="recentHeaders"
          :items="recentRequests"
          :loading="loading"
          density="comfortable"
          :items-per-page="10"
        >
          <template #item.candidate="{ item }">
            <span class="font-weight-medium">
              {{ item.candidate?.first_name }} {{ item.candidate?.last_name }}
            </span>
          </template>
          <template #item.request_type="{ item }">
            <v-chip :color="typeColor(item.request_type)" size="small" variant="tonal">
              {{ item.request_type }}
            </v-chip>
          </template>
          <template #item.status="{ item }">
            <v-chip :color="statusColor(item.status)" size="small" variant="flat">
              <span style="color:white">{{ item.status }}</span>
            </v-chip>
          </template>
          <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
          </template>
        </v-data-table>
      </v-card>
    </v-container>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AppTopBar from '@/components/layout/AppTopBar.vue'
import StatCard from '@/components/reports/StatCard.vue'
import DonutChart from '@/components/reports/DonutChart.vue'
import api from '@/plugins/axios'

const authStore = useAuthStore()
defineEmits(['toggle-drawer'])

const stats = ref(null)
const recentRequests = ref([])
const loading = ref(false)

const today = new Date().toLocaleDateString('en-GB', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })

const statusChartItems = computed(() => [
  { label: 'Active', value: stats.value?.active_candidates || 0 },
  { label: 'Inactive', value: stats.value?.inactive_candidates || 0 },
  { label: 'Placed', value: stats.value?.placed_candidates || 0 },
])

const typeChartItems = computed(() => [
  { label: 'Reserve', value: stats.value?.requests_by_type?.reserve || 0 },
  { label: 'More Info', value: stats.value?.requests_by_type?.more_info || 0 },
  { label: 'Interview', value: stats.value?.requests_by_type?.interview || 0 },
])

const nationalityHeaders = [
  { title: 'Nationality', key: 'nationality', sortable: true },
  { title: 'Count', key: 'count', sortable: true },
]
const occupationHeaders = [
  { title: 'Occupation', key: 'occupation', sortable: true },
  { title: 'Count', key: 'count', sortable: true },
]
const recentHeaders = [
  { title: 'Candidate', key: 'candidate', sortable: false },
  { title: 'Client', key: 'user.name', sortable: false },
  { title: 'Company', key: 'user.company_name', sortable: false },
  { title: 'Type', key: 'request_type', sortable: false },
  { title: 'Date', key: 'created_at', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
]

onMounted(async () => {
  loading.value = true
  try {
    const [statsRes, requestsRes] = await Promise.all([
      api.get('/admin/stats'),
      api.get('/admin/requests', { params: { per_page: 10 } })
    ])
    stats.value = statsRes.data
    recentRequests.value = requestsRes.data.data || []
  } finally {
    loading.value = false
  }
})

function formatDate(d) { return d ? new Date(d).toLocaleDateString('en-GB') : '' }
function typeColor(t) { return { reserve: 'secondary', more_info: 'primary', interview: 'deep-purple' }[t] || 'grey' }
function statusColor(s) { return { pending: '#E65100', processing: '#1565C0', done: '#2E7D32' }[s] || 'grey' }
</script>
