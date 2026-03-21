<template>
  <div>
    <AppTopBar :title="$t('nav.adminRequests')" @toggle-drawer="$emit('toggle-drawer')" />

    <v-container fluid class="pa-6">
      <!-- Filters -->
      <v-card elevation="1" class="mb-4">
        <v-card-text>
          <v-row align="center" dense>
            <v-col cols="12" sm="3">
              <v-select
                v-model="filterStatus"
                :items="statusItems"
                label="Status"
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
                label="Type"
                variant="outlined"
                density="compact"
                hide-details
                clearable
              />
            </v-col>
            <v-col cols="12" sm="2">
              <v-text-field
                v-model="dateFrom"
                label="From"
                type="date"
                variant="outlined"
                density="compact"
                hide-details
              />
            </v-col>
            <v-col cols="12" sm="2">
              <v-text-field
                v-model="dateTo"
                label="To"
                type="date"
                variant="outlined"
                density="compact"
                hide-details
              />
            </v-col>
            <v-col cols="auto">
              <v-btn color="primary" variant="flat" size="small" @click="fetchRequests">Apply</v-btn>
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
          density="comfortable"
        >
          <template #item.id="{ item }">
            <span class="font-weight-bold text-medium-emphasis">#{{ item.id }}</span>
          </template>

          <template #item.candidate="{ item }">
            <div class="py-1">
              <div class="font-weight-medium">{{ item.candidate?.first_name }} {{ item.candidate?.last_name }}</div>
              <div class="text-caption text-medium-emphasis">{{ item.candidate?.occupation }}</div>
            </div>
          </template>

          <template #item.client="{ item }">
            <div>
              <div class="font-weight-medium">{{ item.user?.name }}</div>
              <div class="text-caption text-medium-emphasis">{{ item.user?.company_name }}</div>
            </div>
          </template>

          <template #item.request_type="{ item }">
            <v-chip :color="typeColor(item.request_type)" size="small" variant="tonal">
              {{ item.request_type }}
            </v-chip>
          </template>

          <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
          </template>

          <template #item.status="{ item }">
            <v-select
              v-model="item.status"
              :items="['pending', 'processing', 'done']"
              variant="outlined"
              density="compact"
              hide-details
              style="min-width: 130px"
              @update:model-value="markEdited(item)"
            />
          </template>

          <template #item.admin_note="{ item }">
            <div class="d-flex align-center ga-1">
              <v-text-field
                v-model="item.admin_note"
                variant="outlined"
                density="compact"
                hide-details
                placeholder="Add note..."
                style="min-width: 150px"
                @update:model-value="markEdited(item)"
              />
              <v-btn
                v-if="editedIds.has(item.id)"
                icon="mdi-content-save"
                size="small"
                color="primary"
                variant="flat"
                @click="saveRow(item)"
              />
            </div>
          </template>
        </v-data-table>
      </v-card>
    </v-container>

    <v-snackbar v-model="snackbar" :color="snackbarColor" timeout="3000">{{ snackbarMsg }}</v-snackbar>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import AppTopBar from '@/components/layout/AppTopBar.vue'
import api from '@/plugins/axios'

defineEmits(['toggle-drawer'])

const requests = ref([])
const loading = ref(false)
const editedIds = reactive(new Set())
const filterStatus = ref(null)
const filterType = ref(null)
const dateFrom = ref('')
const dateTo = ref('')
const snackbar = ref(false)
const snackbarMsg = ref('')
const snackbarColor = ref('success')

const statusItems = ['pending', 'processing', 'done']
const typeItems = ['reserve', 'more_info', 'interview']

const headers = [
  { title: 'ID', key: 'id', sortable: true, width: 60 },
  { title: 'Candidate', key: 'candidate', sortable: false },
  { title: 'Client', key: 'client', sortable: false },
  { title: 'Type', key: 'request_type', sortable: true },
  { title: 'Date', key: 'created_at', sortable: true },
  { title: 'Status', key: 'status', sortable: true, width: 150 },
  { title: 'Admin Note', key: 'admin_note', sortable: false, width: 250 },
]

onMounted(fetchRequests)
watch([filterStatus, filterType, dateFrom, dateTo], fetchRequests)

async function fetchRequests() {
  loading.value = true
  try {
    const params = {}
    if (filterStatus.value) params.status = filterStatus.value
    if (filterType.value) params.request_type = filterType.value
    if (dateFrom.value) params.date_from = dateFrom.value
    if (dateTo.value) params.date_to = dateTo.value
    const res = await api.get('/admin/requests', { params })
    requests.value = res.data.data || res.data
    editedIds.clear()
  } finally {
    loading.value = false
  }
}

function markEdited(item) {
  editedIds.add(item.id)
}

async function saveRow(item) {
  try {
    await api.patch(`/requests/${item.id}`, {
      status: item.status,
      admin_note: item.admin_note
    })
    editedIds.delete(item.id)
    showSnackbar('Request updated')
  } catch {
    showSnackbar('Error saving changes', 'error')
  }
}

function formatDate(d) { return d ? new Date(d).toLocaleDateString('en-GB') : '' }
function typeColor(t) { return { reserve: 'secondary', more_info: 'primary', interview: 'deep-purple' }[t] || 'grey' }

function showSnackbar(msg, color = 'success') {
  snackbarMsg.value = msg
  snackbarColor.value = color
  snackbar.value = true
}
</script>
