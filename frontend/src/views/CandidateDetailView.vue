<template>
  <v-layout>
    <AppSidebar v-model="drawer" />
    <v-main>
      <AppTopBar
        :title="candidateName"
        :breadcrumbs="breadcrumbs"
        @toggle-drawer="drawer = !drawer"
      />

      <v-container fluid class="pa-6">
        <div v-if="loading" class="d-flex justify-center pa-10">
          <v-progress-circular indeterminate color="primary" size="64" />
        </div>

        <v-row v-else-if="candidate">
          <!-- Left: Photo & Actions -->
          <v-col cols="12" md="4">
            <v-card elevation="2" class="mb-4">
              <v-card-text class="text-center pa-5">
                <v-avatar size="150" color="grey-lighten-2" class="mb-4">
                  <v-img
                    v-if="candidate.image_path"
                    :src="`${$storageUrl}/candidates/${candidate.image_path}`"
                    cover
                  />
                  <v-icon v-else icon="mdi-account-circle" size="150" color="grey" />
                </v-avatar>

                <div class="d-flex justify-center align-center ga-2 mb-2">
                  <v-chip color="primary" variant="outlined">
                    #{{ String(candidate.id).padStart(3, '0') }}
                  </v-chip>
                  <v-chip v-if="authStore.isAdmin" :color="statusColor" variant="flat" size="small">
                    <span style="color:white">{{ candidate.status }}</span>
                  </v-chip>
                </div>

                <div class="text-h6 font-weight-bold mb-1">
                  {{ candidate.first_name }} {{ candidate.last_name }}
                </div>
                <div class="text-body-2 text-medium-emphasis mb-4">
                  <v-icon size="14" class="mr-1">mdi-stethoscope</v-icon>
                  {{ candidate.occupation }}
                </div>

                <!-- Action buttons (clients only) -->
                <template v-if="!authStore.isAdmin">
                  <v-divider class="mb-3" />
                  <div class="d-flex flex-column ga-2">
                    <v-btn
                      variant="outlined"
                      color="secondary"
                      prepend-icon="mdi-bookmark"
                      :disabled="requestedTypes.includes('reserve')"
                      @click="openRequest('reserve')"
                    >{{ $t('actions.reserve') }}</v-btn>
                    <v-btn
                      variant="outlined"
                      color="primary"
                      prepend-icon="mdi-information"
                      :disabled="requestedTypes.includes('more_info')"
                      @click="openRequest('more_info')"
                    >{{ $t('actions.moreInfo') }}</v-btn>
                    <v-btn
                      variant="outlined"
                      color="deep-purple"
                      prepend-icon="mdi-calendar-account"
                      :disabled="requestedTypes.includes('interview')"
                      @click="openRequest('interview')"
                    >{{ $t('actions.interview') }}</v-btn>
                  </div>
                </template>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Right: Details -->
          <v-col cols="12" md="8">
            <v-expansion-panels v-model="openPanels" multiple>
              <!-- Personal Information -->
              <v-expansion-panel value="personal">
                <v-expansion-panel-title class="text-subtitle-1 font-weight-bold">
                  <v-icon class="mr-2" color="primary">mdi-account</v-icon>
                  {{ $t('reports.personalInfo') }}
                </v-expansion-panel-title>
                <v-expansion-panel-text>
                  <v-table density="compact">
                    <tbody>
                      <tr>
                        <td class="text-medium-emphasis" style="width:180px">{{ $t('candidates.nationality') }}</td>
                        <td class="font-weight-medium">{{ candidate.nationality }}</td>
                      </tr>
                      <tr>
                        <td class="text-medium-emphasis">Date of Birth</td>
                        <td class="font-weight-medium">{{ formatDate(candidate.date_of_birth) }}</td>
                      </tr>
                      <tr>
                        <td class="text-medium-emphasis">{{ $t('candidates.age') }}</td>
                        <td class="font-weight-medium">{{ candidate.age }} years</td>
                      </tr>
                      <tr v-if="candidate.place_of_birth">
                        <td class="text-medium-emphasis">Place of Birth</td>
                        <td class="font-weight-medium">{{ candidate.place_of_birth }}</td>
                      </tr>
                      <tr>
                        <td class="text-medium-emphasis">{{ $t('candidates.countryOfOrigin') }}</td>
                        <td class="font-weight-medium">{{ candidate.country_of_origin }}</td>
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
                        <td class="text-medium-emphasis" style="width:180px">{{ $t('candidates.occupation') }}</td>
                        <td class="font-weight-medium">{{ candidate.occupation }}</td>
                      </tr>
                      <tr>
                        <td class="text-medium-emphasis">{{ $t('candidates.germanLevel') }}</td>
                        <td>
                          <v-chip :color="germanLevelColor" size="small" variant="flat">
                            <span style="color:white">{{ candidate.german_level }}</span>
                          </v-chip>
                        </td>
                      </tr>
                      <tr v-if="candidate.certificate">
                        <td class="text-medium-emphasis">{{ $t('candidates.certificate') }}</td>
                        <td class="font-weight-medium">{{ candidate.certificate }}</td>
                      </tr>
                      <tr v-if="candidate.expose_university_degree">
                        <td class="text-medium-emphasis">{{ $t('candidates.degree') }}</td>
                        <td class="font-weight-medium">{{ candidate.expose_university_degree }}</td>
                      </tr>
                      <tr v-if="candidate.anabin_status">
                        <td class="text-medium-emphasis">{{ $t('candidates.anabin') }}</td>
                        <td class="font-weight-medium">{{ candidate.anabin_status }}</td>
                      </tr>
                    </tbody>
                  </v-table>
                </v-expansion-panel-text>
              </v-expansion-panel>

              <!-- Languages -->
              <v-expansion-panel value="languages">
                <v-expansion-panel-title class="text-subtitle-1 font-weight-bold">
                  <v-icon class="mr-2" color="primary">mdi-translate</v-icon>
                  {{ $t('candidates.languages') }}
                </v-expansion-panel-title>
                <v-expansion-panel-text>
                  <div v-if="candidate.additional_languages && candidate.additional_languages.length">
                    <v-chip
                      v-for="lang in candidate.additional_languages"
                      :key="lang.language"
                      class="ma-1"
                      color="primary"
                      variant="outlined"
                    >
                      {{ lang.language }}: {{ lang.level }}
                    </v-chip>
                  </div>
                  <div v-else class="text-body-2 text-medium-emphasis">No additional languages listed.</div>
                </v-expansion-panel-text>
              </v-expansion-panel>
            </v-expansion-panels>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-layout>

  <RequestDialog
    v-model="requestDialog"
    :candidate="candidate"
    :request-type="selectedType"
    @success="onRequestSuccess"
  />

  <v-snackbar v-model="snackbar" color="success" timeout="3000">
    {{ $t('actions.successMsg') }}
  </v-snackbar>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppTopBar from '@/components/layout/AppTopBar.vue'
import RequestDialog from '@/components/RequestDialog.vue'
import api from '@/plugins/axios'

const route = useRoute()
const authStore = useAuthStore()
const { t } = useI18n()

const drawer = ref(false)
const loading = ref(true)
const candidate = ref(null)
const requestDialog = ref(false)
const selectedType = ref('')
const snackbar = ref(false)
const myRequests = ref([])
const openPanels = ref(['personal', 'professional', 'languages'])

const candidateName = computed(() =>
  candidate.value ? `${candidate.value.first_name} ${candidate.value.last_name}` : ''
)

const breadcrumbs = computed(() => [
  { title: t('candidates.title'), to: '/candidates' },
  { title: candidateName.value }
])

const statusColor = computed(() => ({
  Active: 'success',
  Inactive: 'warning',
  Placed: 'primary'
}[candidate.value?.status] || 'grey'))

const germanLevelColor = computed(() => {
  const level = candidate.value?.german_level
  if (['C1', 'C2', 'Native'].includes(level)) return '#2E7D32'
  if (['B1', 'B2'].includes(level)) return '#1565C0'
  if (['A1', 'A2'].includes(level)) return '#E65100'
  return 'grey'
})

const requestedTypes = computed(() =>
  myRequests.value
    .filter(r => r.candidate_id === candidate.value?.id && ['pending', 'processing'].includes(r.status))
    .map(r => r.request_type)
)

onMounted(async () => {
  await Promise.all([fetchCandidate(), fetchMyRequests()])
})

async function fetchCandidate() {
  loading.value = true
  try {
    const res = await api.get(`/candidates/${route.params.id}`)
    candidate.value = res.data
  } finally {
    loading.value = false
  }
}

async function fetchMyRequests() {
  try {
    const res = await api.get('/requests')
    myRequests.value = res.data.data || []
  } catch {}
}

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-GB')
}

function openRequest(type) {
  selectedType.value = type
  requestDialog.value = true
}

function onRequestSuccess() {
  snackbar.value = true
  fetchMyRequests()
}
</script>
