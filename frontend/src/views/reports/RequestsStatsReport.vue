<template>
  <div>
    <div v-if="loading" class="d-flex justify-center pa-10">
      <v-progress-circular indeterminate color="primary" size="64" />
    </div>

    <template v-else-if="stats">
      <!-- Row 1: Stat Cards -->
      <v-row class="mb-4">
        <v-col cols="12" sm="6" lg="3">
          <StatCard
            :value="stats.total_requests"
            :label="$t('reports.totalRequests')"
            icon="mdi-clipboard-list"
            icon-color="#1565C0"
          />
        </v-col>
        <v-col cols="12" sm="6" lg="3">
          <StatCard
            :value="stats.by_type?.reserve || 0"
            label="Reserved"
            icon="mdi-bookmark"
            icon-color="#00897B"
          />
        </v-col>
        <v-col cols="12" sm="6" lg="3">
          <StatCard
            :value="stats.by_type?.more_info || 0"
            label="More Info"
            icon="mdi-information"
            icon-color="#1565C0"
          />
        </v-col>
        <v-col cols="12" sm="6" lg="3">
          <StatCard
            :value="stats.by_type?.interview || 0"
            label="Interviews"
            icon="mdi-calendar-account"
            icon-color="#6A1B9A"
          />
        </v-col>
      </v-row>

      <!-- Row 2: Charts -->
      <v-row class="mb-4">
        <v-col cols="12" md="4">
          <DonutChart
            :title="$t('reports.byType')"
            :items="typeChartItems"
            :colors="['#00897B', '#1565C0', '#6A1B9A']"
          />
        </v-col>
        <v-col cols="12" md="4">
          <DonutChart
            :title="$t('reports.byStatus')"
            :items="statusChartItems"
            :colors="['#E65100', '#1565C0', '#2E7D32']"
          />
        </v-col>
        <v-col v-if="isAdmin" cols="12" md="4">
          <BarChart
            :title="$t('reports.byMonth')"
            :labels="monthLabels"
            :data="monthData"
            color="#1565C0"
          />
        </v-col>
      </v-row>

      <!-- Row 3: Top Candidates -->
      <v-card elevation="2">
        <v-card-title class="pa-4">{{ $t('reports.topCandidates') }}</v-card-title>
        <v-data-table
          :headers="topHeaders"
          :items="stats.top_requested_candidates || []"
          :items-per-page="-1"
          hide-default-footer
          density="comfortable"
        >
          <template #item.rank="{ index }">
            <v-chip
              :color="index === 0 ? '#B8860B' : 'grey'"
              variant="flat"
              size="small"
            >
              <span style="color:white">{{ index + 1 }}</span>
            </v-chip>
          </template>
          <template #item.id="{ item }">
            <span class="font-weight-bold" style="color:#1565C0">
              #{{ String(item.id).padStart(3, '0') }}
            </span>
          </template>
          <template #item.name="{ item }">
            <span class="font-weight-medium">{{ item.first_name }} {{ item.last_name }}</span>
          </template>
        </v-data-table>
      </v-card>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useReportStore } from '@/stores/reports'
import StatCard from '@/components/reports/StatCard.vue'
import DonutChart from '@/components/reports/DonutChart.vue'
import BarChart from '@/components/reports/BarChart.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const authStore = useAuthStore()
const reportStore = useReportStore()
const isAdmin = computed(() => authStore.isAdmin)
const stats = computed(() => reportStore.requestStats)
const loading = computed(() => reportStore.loading)

onMounted(() => reportStore.fetchRequestStats())

const typeChartItems = computed(() => [
  { label: 'Reserve', value: stats.value?.by_type?.reserve || 0 },
  { label: 'More Info', value: stats.value?.by_type?.more_info || 0 },
  { label: 'Interview', value: stats.value?.by_type?.interview || 0 },
])

const statusChartItems = computed(() => [
  { label: 'Pending', value: stats.value?.by_status?.pending || 0 },
  { label: 'Processing', value: stats.value?.by_status?.processing || 0 },
  { label: 'Done', value: stats.value?.by_status?.done || 0 },
])

const monthLabels = computed(() => (stats.value?.by_month || []).map(m => m.month))
const monthData = computed(() => (stats.value?.by_month || []).map(m => m.count))

const topHeaders = [
  { title: 'Rank', key: 'rank', sortable: false, width: 70 },
  { title: 'ID', key: 'id', sortable: false, width: 80 },
  { title: 'Name', key: 'name', sortable: false },
  { title: 'Occupation', key: 'occupation', sortable: false },
  { title: 'Reserve', key: 'reserve', sortable: true, width: 90 },
  { title: 'More Info', key: 'more_info', sortable: true, width: 90 },
  { title: 'Interview', key: 'interview', sortable: true, width: 90 },
  { title: 'Total', key: 'count', sortable: true, width: 80 },
]
</script>
