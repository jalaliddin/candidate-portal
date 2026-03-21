<template>
  <div>
    <!-- Candidate selector -->
    <v-card elevation="1" class="mb-4">
      <v-card-text>
        <v-autocomplete
          v-model="selectedId"
          :items="candidateSearchItems"
          :loading="searchLoading"
          :label="$t('reports.selectCandidate')"
          item-title="label"
          item-value="id"
          variant="outlined"
          density="compact"
          prepend-inner-icon="mdi-magnify"
          hide-details
          clearable
          @update:search="onSearch"
        />
      </v-card-text>
    </v-card>

    <!-- Loading -->
    <div v-if="reportStore.loading" class="d-flex justify-center pa-10">
      <v-progress-circular indeterminate color="primary" size="64" />
    </div>

    <!-- Profile -->
    <template v-else-if="profile">
      <div class="d-flex justify-end mb-3">
        <v-btn prepend-icon="mdi-printer" variant="outlined" @click="window.print()">
          {{ $t('common.print') }}
        </v-btn>
      </div>

      <v-row>
        <!-- Left: Profile card -->
        <v-col cols="12" md="4">
          <CandidateProfileCard
            :candidate="profile.candidate"
            :requested-types="requestedTypes"
            :show-actions="!isAdmin"
            @request="openRequest"
          />
        </v-col>

        <!-- Right: Info panels + history -->
        <v-col cols="12" md="8">
          <v-expansion-panels v-model="openPanels" multiple>
            <!-- Personal Info -->
            <v-expansion-panel value="personal">
              <v-expansion-panel-title class="text-subtitle-1 font-weight-bold">
                <v-icon class="mr-2" color="primary">mdi-account</v-icon>
                {{ $t('reports.personalInfo') }}
              </v-expansion-panel-title>
              <v-expansion-panel-text>
                <v-table density="compact">
                  <tbody>
                    <tr>
                      <td class="text-medium-emphasis" style="width:170px">Nationality</td>
                      <td>{{ profile.candidate.nationality }}</td>
                    </tr>
                    <tr>
                      <td class="text-medium-emphasis">Date of Birth</td>
                      <td>{{ formatDate(profile.candidate.date_of_birth) }}</td>
                    </tr>
                    <tr>
                      <td class="text-medium-emphasis">Age</td>
                      <td>{{ profile.candidate.age }} years</td>
                    </tr>
                    <tr v-if="profile.candidate.place_of_birth">
                      <td class="text-medium-emphasis">Place of Birth</td>
                      <td>{{ profile.candidate.place_of_birth }}</td>
                    </tr>
                    <tr>
                      <td class="text-medium-emphasis">Country of Origin</td>
                      <td>{{ profile.candidate.country_of_origin }}</td>
                    </tr>
                  </tbody>
                </v-table>
              </v-expansion-panel-text>
            </v-expansion-panel>

            <!-- Professional Profile -->
            <v-expansion-panel value="professional">
              <v-expansion-panel-title class="text-subtitle-1 font-weight-bold">
                <v-icon class="mr-2" color="primary">mdi-briefcase</v-icon>
                {{ $t('reports.professionalInfo') }}
              </v-expansion-panel-title>
              <v-expansion-panel-text>
                <v-table density="compact">
                  <tbody>
                    <tr>
                      <td class="text-medium-emphasis" style="width:170px">Occupation</td>
                      <td>{{ profile.candidate.occupation }}</td>
                    </tr>
                    <tr>
                      <td class="text-medium-emphasis">German Level</td>
                      <td>
                        <v-chip :color="germanLevelColor(profile.candidate.german_level)" size="small" variant="flat">
                          <span style="color:white">{{ profile.candidate.german_level }}</span>
                        </v-chip>
                      </td>
                    </tr>
                    <tr>
                      <td class="text-medium-emphasis">Languages</td>
                      <td>
                        <div class="d-flex flex-wrap ga-1 py-1">
                          <v-chip
                            v-for="lang in (profile.candidate.additional_languages || [])"
                            :key="lang.language"
                            size="x-small"
                            variant="outlined"
                            color="primary"
                          >{{ lang.language }}: {{ lang.level }}</v-chip>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="profile.candidate.certificate">
                      <td class="text-medium-emphasis">Certificate</td>
                      <td>{{ profile.candidate.certificate }}</td>
                    </tr>
                    <tr v-if="profile.candidate.expose_university_degree">
                      <td class="text-medium-emphasis">University Degree</td>
                      <td>{{ profile.candidate.expose_university_degree }}</td>
                    </tr>
                    <tr v-if="profile.candidate.anabin_status">
                      <td class="text-medium-emphasis">Anabin Status</td>
                      <td>{{ profile.candidate.anabin_status }}</td>
                    </tr>
                  </tbody>
                </v-table>
              </v-expansion-panel-text>
            </v-expansion-panel>

            <!-- Request History -->
            <v-expansion-panel value="history">
              <v-expansion-panel-title class="text-subtitle-1 font-weight-bold">
                <v-icon class="mr-2" color="primary">mdi-history</v-icon>
                {{ $t('reports.requestHistory') }}
              </v-expansion-panel-title>
              <v-expansion-panel-text>
                <!-- Client: timeline -->
                <template v-if="!isAdmin">
                  <div v-if="!profile.requests || !profile.requests.length" class="text-body-2 text-medium-emphasis pa-2">
                    {{ $t('reports.noRequestsYet') }}
                  </div>
                  <v-timeline v-else density="compact" side="end">
                    <v-timeline-item
                      v-for="req in profile.requests"
                      :key="req.id"
                      :dot-color="typeColor(req.request_type)"
                      size="small"
                    >
                      <div class="d-flex align-center ga-2 flex-wrap">
                        <v-chip :color="typeColor(req.request_type)" size="small" variant="tonal">
                          {{ req.request_type }}
                        </v-chip>
                        <v-chip :color="statusColor(req.status)" size="small" variant="flat">
                          <span style="color:white">{{ req.status }}</span>
                        </v-chip>
                        <span class="text-caption text-medium-emphasis">{{ formatDate(req.created_at) }}</span>
                      </div>
                      <div v-if="req.message" class="text-body-2 text-medium-emphasis mt-1">{{ req.message }}</div>
                    </v-timeline-item>
                  </v-timeline>
                </template>

                <!-- Admin: table -->
                <template v-else>
                  <v-data-table
                    v-if="profile.requests && profile.requests.length"
                    :headers="adminRequestHeaders"
                    :items="profile.requests"
                    density="compact"
                    :items-per-page="-1"
                    hide-default-footer
                  >
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
                  <div v-else class="text-body-2 text-medium-emphasis pa-2">No requests for this candidate.</div>
                </template>
              </v-expansion-panel-text>
            </v-expansion-panel>
          </v-expansion-panels>
        </v-col>
      </v-row>
    </template>
  </div>

  <RequestDialog
    v-model="requestDialog"
    :candidate="profile?.candidate"
    :request-type="requestType"
    @success="onRequestSuccess"
  />
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useReportStore } from '@/stores/reports'
import CandidateProfileCard from '@/components/reports/CandidateProfileCard.vue'
import RequestDialog from '@/components/RequestDialog.vue'
import api from '@/plugins/axios'

const authStore = useAuthStore()
const reportStore = useReportStore()
const isAdmin = computed(() => authStore.isAdmin)

const selectedId = ref(null)
const candidateSearchItems = ref([])
const searchLoading = ref(false)
const requestDialog = ref(false)
const requestType = ref('')
const openPanels = ref(['personal', 'professional', 'history'])

const profile = computed(() => reportStore.candidateProfile)

const requestedTypes = computed(() => {
  if (!profile.value?.requests || isAdmin.value) return []
  return profile.value.requests
    .filter(r => ['pending', 'processing'].includes(r.status))
    .map(r => r.request_type)
})

watch(selectedId, (id) => {
  if (id) reportStore.fetchCandidateProfile(id)
  else reportStore.candidateProfile = null
})

async function onSearch(val) {
  if (!val || val.length < 2) return
  searchLoading.value = true
  try {
    const res = await api.get('/candidates', { params: { search: val } })
    candidateSearchItems.value = (res.data.data || []).map(c => ({
      id: c.id,
      label: `#${String(c.id).padStart(3,'0')} ${c.first_name} ${c.last_name} — ${c.occupation}`
    }))
  } finally {
    searchLoading.value = false
  }
}

function openRequest({ type }) {
  requestType.value = type
  requestDialog.value = true
}

function onRequestSuccess() {
  if (selectedId.value) reportStore.fetchCandidateProfile(selectedId.value)
}

function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('en-GB') : ''
}

function germanLevelColor(level) {
  if (['C1', 'C2', 'Native'].includes(level)) return '#2E7D32'
  if (['B1', 'B2'].includes(level)) return '#1565C0'
  if (['A1', 'A2'].includes(level)) return '#E65100'
  return 'grey'
}

function typeColor(type) {
  return { reserve: 'secondary', more_info: 'primary', interview: 'deep-purple' }[type] || 'grey'
}

function statusColor(status) {
  return { pending: '#E65100', processing: '#1565C0', done: '#2E7D32' }[status] || 'grey'
}

const adminRequestHeaders = [
  { title: 'Client', key: 'user.name', sortable: false },
  { title: 'Company', key: 'user.company_name', sortable: false },
  { title: 'Type', key: 'request_type', sortable: true },
  { title: 'Date', key: 'created_at', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
]
</script>
