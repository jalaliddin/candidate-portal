<template>
  <div>
    <!-- Filter bar -->
    <v-card elevation="1" class="mb-4">
      <v-card-text>
        <v-row align="center" dense>
          <v-col cols="12" sm="3">
            <v-autocomplete
              v-model="filters.occupation"
              :items="filterOptions.occupations"
              label="Occupation"
              variant="outlined"
              density="compact"
              hide-details
              clearable
            />
          </v-col>
          <v-col cols="12" sm="3">
            <v-select
              v-model="filters.german_level"
              :items="germanLevels"
              label="German Level"
              variant="outlined"
              density="compact"
              hide-details
              clearable
            />
          </v-col>
          <v-col cols="12" sm="3">
            <v-autocomplete
              v-model="filters.nationality"
              :items="filterOptions.nationalities"
              label="Nationality"
              variant="outlined"
              density="compact"
              hide-details
              clearable
            />
          </v-col>
          <v-col v-if="isAdmin" cols="12" sm="2">
            <v-select
              v-model="filters.status"
              :items="statusItems"
              label="Status"
              variant="outlined"
              density="compact"
              hide-details
              clearable
            />
          </v-col>
          <v-col cols="12" :sm="isAdmin ? 1 : 3" class="d-flex ga-2">
            <v-btn color="primary" variant="flat" @click="fetchList" size="small">Apply</v-btn>
            <v-btn variant="outlined" @click="resetFilters" size="small">Reset</v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Table -->
    <v-card elevation="2">
      <v-card-text class="pa-0">
        <div class="d-flex align-center justify-space-between pa-4">
          <div class="text-body-2 text-medium-emphasis">
            {{ reportStore.candidateList.length }} candidates · {{ $t('reports.clickToView') }}
          </div>
        </div>

        <v-data-table
          :headers="headers"
          :items="reportStore.candidateList"
          :loading="reportStore.loading"
          :items-per-page="10"
          :items-per-page-options="[10, 25, 50]"
          hover
          @click:row="(_, { item }) => navigateToCandidate(item)"
        >
          <!-- ID -->
          <template #item.id="{ item }">
            <span class="font-weight-bold" style="color:#1565C0">
              #{{ String(item.id).padStart(3, '0') }}
            </span>
          </template>

          <!-- Photo -->
          <template #item.photo="{ item }">
            <v-avatar size="32" color="grey-lighten-2" class="my-1">
              <v-img
                v-if="item.image_path"
                :src="`http://localhost:8000/storage/candidates/${item.image_path}`"
                cover
              />
              <v-icon v-else icon="mdi-account" size="20" color="grey" />
            </v-avatar>
          </template>

          <!-- Name -->
          <template #item.name="{ item }">
            <span class="font-weight-medium">{{ item.first_name }} {{ item.last_name }}</span>
          </template>

          <!-- Age -->
          <template #item.age="{ item }">{{ item.age }}</template>

          <!-- German Level -->
          <template #item.german_level="{ item }">
            <v-chip :color="germanLevelColor(item.german_level)" size="small" variant="flat">
              <span style="color:white">{{ item.german_level }}</span>
            </v-chip>
          </template>

          <!-- Languages -->
          <template #item.languages="{ item }">
            <div class="d-flex flex-wrap ga-1 py-1">
              <v-chip
                v-for="(lang, i) in (item.additional_languages || []).slice(0, 2)"
                :key="i"
                size="x-small"
                variant="outlined"
                color="primary"
              >{{ lang.language }}</v-chip>
            </div>
          </template>

          <!-- Certificate -->
          <template #item.certificate="{ item }">
            <span class="text-body-2" style="max-width:150px; display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap">
              {{ item.certificate || '—' }}
            </span>
          </template>

          <!-- Status (admin only) -->
          <template v-if="isAdmin" #item.status="{ item }">
            <v-chip :color="statusColor(item.status)" size="small" variant="flat">
              <span style="color:white">{{ item.status }}</span>
            </v-chip>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useReportStore } from '@/stores/reports'
import { useCandidateStore } from '@/stores/candidates'

const router = useRouter()
const authStore = useAuthStore()
const reportStore = useReportStore()
const candidateStore = useCandidateStore()

const isAdmin = computed(() => authStore.isAdmin)
const filterOptions = computed(() => candidateStore.filterOptions)

const germanLevels = ['None', 'A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'Native']
const statusItems = ['Active', 'Inactive', 'Placed']

const filters = ref({
  occupation: null,
  german_level: null,
  nationality: null,
  status: null
})

const headers = computed(() => {
  const cols = [
    { title: 'ID', key: 'id', sortable: true, width: 80 },
    { title: 'Photo', key: 'photo', sortable: false, width: 60 },
    { title: 'Name', key: 'name', sortable: true },
    { title: 'Nationality', key: 'nationality', sortable: true },
    { title: 'Age', key: 'age', sortable: true, width: 70 },
    { title: 'Occupation', key: 'occupation', sortable: true },
    { title: 'German', key: 'german_level', sortable: true },
    { title: 'Languages', key: 'languages', sortable: false },
    { title: 'Certificate', key: 'certificate', sortable: false },
  ]
  if (isAdmin.value) cols.push({ title: 'Status', key: 'status', sortable: true })
  return cols
})

onMounted(async () => {
  await candidateStore.fetchFilterOptions()
  await fetchList()
})

async function fetchList() {
  const params = {}
  if (filters.value.occupation) params.occupation = [filters.value.occupation]
  if (filters.value.german_level) params.german_level = [filters.value.german_level]
  if (filters.value.nationality) params.nationality = [filters.value.nationality]
  if (filters.value.status && isAdmin.value) params.status = filters.value.status
  await reportStore.fetchCandidateList(params)
}

function resetFilters() {
  filters.value = { occupation: null, german_level: null, nationality: null, status: null }
  fetchList()
}

function navigateToCandidate(item) {
  router.push(`/candidates/${item.id}`)
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
