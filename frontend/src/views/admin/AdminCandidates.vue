<template>
  <div>
    <AppTopBar :title="$t('nav.adminCandidates')" @toggle-drawer="$emit('toggle-drawer')">
      <template #append>
        <v-btn
          color="primary"
          variant="flat"
          prepend-icon="mdi-plus"
          class="mr-3"
          @click="openCreate"
        >
          {{ $t('admin.addCandidate') }}
        </v-btn>
      </template>
    </AppTopBar>

    <v-container fluid class="pa-6">
      <!-- Filters -->
      <v-card elevation="1" class="mb-4">
        <v-card-text>
          <v-row align="center" dense>
            <v-col cols="12" sm="4">
              <v-text-field
                v-model="search"
                label="Search"
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                density="compact"
                hide-details
                clearable
                @keyup.enter="fetchCandidates"
              />
            </v-col>
            <v-col cols="12" sm="3">
              <v-select
                v-model="filterStatus"
                :items="['Active', 'Inactive', 'Placed']"
                label="Status"
                variant="outlined"
                density="compact"
                hide-details
                clearable
              />
            </v-col>
            <v-col cols="auto">
              <v-btn color="primary" variant="flat" @click="fetchCandidates" size="small">Apply</v-btn>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>

      <!-- Table -->
      <v-card elevation="2">
        <v-data-table
          :headers="headers"
          :items="candidates"
          :loading="loading"
          hover
          item-value="id"
        >
          <template #item.photo="{ item }">
            <v-avatar size="36" color="grey-lighten-2" class="my-1">
              <v-img
                v-if="item.image_path"
                :src="`${$storageUrl}/candidates/${item.image_path}`"
                cover
              />
              <v-icon v-else icon="mdi-account" size="22" color="grey" />
            </v-avatar>
          </template>

          <template #item.id="{ item }">
            <span class="font-weight-bold" style="color:#1565C0">
              #{{ String(item.id).padStart(3, '0') }}
            </span>
          </template>

          <template #item.name="{ item }">
            <span class="font-weight-medium">{{ item.first_name }} {{ item.last_name }}</span>
          </template>

          <template #item.german_level="{ item }">
            <v-chip :color="germanLevelColor(item.german_level)" size="small" variant="flat">
              <span style="color:white">{{ item.german_level }}</span>
            </v-chip>
          </template>

          <template #item.status="{ item }">
            <v-chip
              :color="statusColor(item.status)"
              size="small"
              variant="flat"
              class="cursor-pointer"
              @click.stop="cycleStatus(item)"
            >
              <span style="color:white">{{ item.status }}</span>
              <v-icon end size="12">mdi-refresh</v-icon>
            </v-chip>
          </template>

          <template #item.actions="{ item }">
            <v-btn
              icon="mdi-pencil"
              variant="text"
              size="small"
              color="primary"
              @click.stop="openEdit(item)"
            />
            <v-btn
              icon="mdi-archive"
              variant="text"
              size="small"
              color="error"
              :disabled="item.status === 'Inactive'"
              @click.stop="deactivate(item)"
            />
          </template>
        </v-data-table>
      </v-card>
    </v-container>

    <!-- Form Dialog -->
    <CandidateFormDialog
      v-model="formDialog"
      :candidate="editingCandidate"
      @saved="onSaved"
    />

    <!-- Confirm deactivate dialog -->
    <v-dialog v-model="confirmDialog" max-width="400">
      <v-card>
        <v-card-text class="pa-5">{{ $t('admin.confirmDelete') }}</v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="confirmDialog = false">{{ $t('common.cancel') }}</v-btn>
          <v-btn color="error" variant="flat" @click="confirmDeactivate">{{ $t('admin.deleteCandidate') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-snackbar v-model="snackbar" :color="snackbarColor" timeout="3000">{{ snackbarMsg }}</v-snackbar>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppTopBar from '@/components/layout/AppTopBar.vue'
import CandidateFormDialog from '@/components/CandidateFormDialog.vue'
import api from '@/plugins/axios'

defineEmits(['toggle-drawer'])

const candidates = ref([])
const loading = ref(false)
const search = ref('')
const filterStatus = ref(null)
const formDialog = ref(false)
const editingCandidate = ref(null)
const confirmDialog = ref(false)
const deactivateTarget = ref(null)
const snackbar = ref(false)
const snackbarMsg = ref('')
const snackbarColor = ref('success')

const headers = [
  { title: 'ID', key: 'id', sortable: true, width: 80 },
  { title: 'Photo', key: 'photo', sortable: false, width: 60 },
  { title: 'Name', key: 'name', sortable: true },
  { title: 'Nationality', key: 'nationality', sortable: true },
  { title: 'Occupation', key: 'occupation', sortable: true },
  { title: 'German', key: 'german_level', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, width: 100 },
]

onMounted(fetchCandidates)

async function fetchCandidates() {
  loading.value = true
  try {
    const params = {}
    if (search.value) params.search = search.value
    if (filterStatus.value) params.status = filterStatus.value
    const res = await api.get('/admin/candidates', { params })
    candidates.value = res.data.data || res.data
  } finally { loading.value = false }
}

function openCreate() {
  editingCandidate.value = null
  formDialog.value = true
}

function openEdit(candidate) {
  editingCandidate.value = candidate
  formDialog.value = true
}

function deactivate(candidate) {
  deactivateTarget.value = candidate
  confirmDialog.value = true
}

async function confirmDeactivate() {
  try {
    await api.delete(`/admin/candidates/${deactivateTarget.value.id}`)
    confirmDialog.value = false
    showSnackbar('Candidate deactivated', 'success')
    fetchCandidates()
  } catch {
    showSnackbar('Error deactivating candidate', 'error')
  }
}

async function cycleStatus(candidate) {
  const cycle = { Active: 'Inactive', Inactive: 'Placed', Placed: 'Active' }
  const newStatus = cycle[candidate.status]
  try {
    await api.put(`/admin/candidates/${candidate.id}`, { status: newStatus })
    candidate.status = newStatus
    showSnackbar(`Status updated to ${newStatus}`, 'success')
  } catch {
    showSnackbar('Error updating status', 'error')
  }
}

function onSaved() {
  showSnackbar('Candidate saved successfully', 'success')
  fetchCandidates()
}

function showSnackbar(msg, color = 'success') {
  snackbarMsg.value = msg
  snackbarColor.value = color
  snackbar.value = true
}

function germanLevelColor(level) {
  if (['C1', 'C2', 'Native'].includes(level)) return '#2E7D32'
  if (['B1', 'B2'].includes(level)) return '#1565C0'
  if (['A1', 'A2'].includes(level)) return '#E65100'
  return 'grey'
}

function statusColor(status) {
  return { Active: 'success', Inactive: 'warning', Placed: 'primary' }[status] || 'grey'
}
</script>
