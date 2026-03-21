<template>
  <v-layout>
    <AppSidebar v-model="drawer" />
    <v-main>
      <AppTopBar :title="$t('requests.title')" @toggle-drawer="drawer = !drawer" />

      <v-container fluid class="pa-6">
        <!-- Filters -->
        <v-card elevation="1" class="mb-4">
          <v-card-text>
            <v-row align="center" dense>
              <v-col cols="12" sm="3">
                <v-select
                  v-model="filterStatus"
                  :items="statusItems"
                  :label="$t('requests.status')"
                  variant="outlined"
                  density="compact"
                  hide-details
                  clearable
                />
              </v-col>
              <v-col cols="12" sm="3">
                <v-select
                  v-model="filterType"
                  :items="typeItems"
                  :label="$t('requests.type')"
                  variant="outlined"
                  density="compact"
                  hide-details
                  clearable
                />
              </v-col>
              <v-col cols="12" sm="3">
                <v-text-field
                  v-model="filterDateFrom"
                  :label="$t('common.from')"
                  type="date"
                  variant="outlined"
                  density="compact"
                  hide-details
                />
              </v-col>
              <v-col cols="12" sm="3">
                <v-text-field
                  v-model="filterDateTo"
                  :label="$t('common.to')"
                  type="date"
                  variant="outlined"
                  density="compact"
                  hide-details
                />
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <!-- Table -->
        <v-card elevation="2">
          <v-data-table
            :headers="headers"
            :items="requests"
            :loading="loading"
            hover
            item-value="id"
          >
            <!-- Candidate column -->
            <template #item.candidate="{ item }">
              <div class="d-flex align-center ga-2 py-2">
                <v-avatar size="32" color="grey-lighten-2">
                  <v-img
                    v-if="item.candidate?.image_path"
                    :src="`http://localhost:8000/storage/candidates/${item.candidate.image_path}`"
                    cover
                  />
                  <v-icon v-else icon="mdi-account" size="20" color="grey" />
                </v-avatar>
                <div>
                  <div class="text-body-2 font-weight-medium">
                    {{ item.candidate?.first_name }} {{ item.candidate?.last_name }}
                  </div>
                  <div class="text-caption text-medium-emphasis">
                    #{{ String(item.candidate?.id || 0).padStart(3, '0') }}
                  </div>
                </div>
              </div>
            </template>

            <!-- Type column -->
            <template #item.request_type="{ item }">
              <v-chip :color="typeColor(item.request_type)" size="small" variant="tonal">
                {{ $t(`requests.${item.request_type}`) }}
              </v-chip>
            </template>

            <!-- Date column -->
            <template #item.created_at="{ item }">
              {{ formatDate(item.created_at) }}
            </template>

            <!-- Status column -->
            <template #item.status="{ item }">
              <v-chip :color="statusColor(item.status)" size="small" variant="flat">
                <span style="color:white">{{ $t(`requests.${item.status}`) }}</span>
              </v-chip>
            </template>

            <!-- Message column -->
            <template #item.message="{ item }">
              <span class="text-body-2 text-medium-emphasis">{{ item.message || '—' }}</span>
            </template>

            <!-- Empty state -->
            <template #no-data>
              <div class="text-center pa-10">
                <v-icon icon="mdi-clipboard-list-outline" size="64" color="grey-lighten-2" />
                <div class="text-body-1 text-medium-emphasis mt-3">No requests yet</div>
              </div>
            </template>
          </v-data-table>
        </v-card>
      </v-container>
    </v-main>
  </v-layout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppTopBar from '@/components/layout/AppTopBar.vue'
import api from '@/plugins/axios'

const { t } = useI18n()
const drawer = ref(false)
const loading = ref(false)
const requests = ref([])
const filterStatus = ref(null)
const filterType = ref(null)
const filterDateFrom = ref('')
const filterDateTo = ref('')

const statusItems = [
  { title: t('requests.all'), value: null },
  { title: t('requests.pending'), value: 'pending' },
  { title: t('requests.processing'), value: 'processing' },
  { title: t('requests.done'), value: 'done' },
]

const typeItems = [
  { title: t('common.all'), value: null },
  { title: t('requests.reserve'), value: 'reserve' },
  { title: t('requests.more_info'), value: 'more_info' },
  { title: t('requests.interview'), value: 'interview' },
]

const headers = computed(() => [
  { title: t('requests.candidate'), key: 'candidate', sortable: false },
  { title: t('requests.type'), key: 'request_type', sortable: false },
  { title: t('requests.date'), key: 'created_at', sortable: true },
  { title: t('requests.status'), key: 'status', sortable: true },
  { title: t('requests.note'), key: 'message', sortable: false },
])

onMounted(fetchRequests)

watch([filterStatus, filterType, filterDateFrom, filterDateTo], fetchRequests)

async function fetchRequests() {
  loading.value = true
  try {
    const params = {}
    if (filterStatus.value) params.status = filterStatus.value
    if (filterType.value) params.request_type = filterType.value
    if (filterDateFrom.value) params.date_from = filterDateFrom.value
    if (filterDateTo.value) params.date_to = filterDateTo.value
    const res = await api.get('/requests', { params })
    requests.value = res.data.data || res.data
  } finally {
    loading.value = false
  }
}

function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('en-GB') : ''
}

function typeColor(type) {
  return { reserve: 'secondary', more_info: 'primary', interview: 'deep-purple' }[type] || 'grey'
}

function statusColor(status) {
  return { pending: '#E65100', processing: '#1565C0', done: '#2E7D32' }[status] || 'grey'
}
</script>
