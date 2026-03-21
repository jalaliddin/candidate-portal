<template>
  <v-layout>
    <AppSidebar v-model="drawer" />
    <v-main>
      <AppTopBar
        :title="$t('candidates.title')"
        @toggle-drawer="drawer = !drawer"
      >
        <template #append>
          <v-chip v-if="!loading" color="primary" size="small" class="mr-2">
            {{ store.pagination.total }} candidates
          </v-chip>
        </template>
      </AppTopBar>

      <v-container fluid class="pa-4">
        <v-row>
          <!-- Filter Panel (desktop) -->
          <v-col v-if="!isMobile" cols="12" md="3" lg="2">
            <FilterPanel
              :filters="store.filters"
              :options="store.filterOptions"
              @update:filters="updateFilters"
              @apply="applyFilters"
              @reset="resetFilters"
            />
          </v-col>

          <!-- Main Content -->
          <v-col cols="12" :md="isMobile ? 12 : 9" :lg="isMobile ? 12 : 10">
            <!-- Search bar row -->
            <div class="d-flex align-center ga-3 mb-4 flex-wrap">
              <v-text-field
                v-model="searchInput"
                :placeholder="$t('candidates.searchPlaceholder')"
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                density="compact"
                hide-details
                clearable
                style="max-width: 400px; flex: 1"
                @keyup.enter="applyFilters"
                @click:clear="clearSearch"
              />

              <!-- Mobile filter button -->
              <FilterPanel
                v-if="isMobile"
                :filters="store.filters"
                :options="store.filterOptions"
                @update:filters="updateFilters"
                @apply="applyFilters"
                @reset="resetFilters"
              />
            </div>

            <!-- Results info -->
            <div v-if="!loading && store.pagination.total > 0" class="text-body-2 text-medium-emphasis mb-3">
              {{
                $t('candidates.showing', {
                  from: (store.pagination.current_page - 1) * store.pagination.per_page + 1,
                  to: Math.min(store.pagination.current_page * store.pagination.per_page, store.pagination.total),
                  total: store.pagination.total
                })
              }}
            </div>

            <!-- Skeleton loaders -->
            <v-row v-if="loading">
              <v-col v-for="n in 12" :key="n" cols="12" sm="6" lg="4">
                <v-skeleton-loader type="card" height="340" />
              </v-col>
            </v-row>

            <!-- Candidate grid -->
            <v-row v-else-if="store.candidates.length > 0">
              <v-col
                v-for="candidate in store.candidates"
                :key="candidate.id"
                cols="12"
                sm="6"
                lg="4"
              >
                <CandidateCard
                  :candidate="candidate"
                  :requested-types="getRequestedTypes(candidate.id)"
                  @request="openRequestDialog"
                />
              </v-col>
            </v-row>

            <!-- Empty state -->
            <div v-else class="text-center py-16">
              <v-icon icon="mdi-account-search" size="96" color="grey-lighten-1" />
              <div class="text-h6 text-medium-emphasis mt-4">{{ $t('candidates.noResults') }}</div>
            </div>

            <!-- Pagination -->
            <div v-if="store.pagination.last_page > 1" class="d-flex justify-center mt-6">
              <v-pagination
                v-model="currentPage"
                :length="store.pagination.last_page"
                :total-visible="7"
                color="primary"
                @update:model-value="changePage"
              />
            </div>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-layout>

  <!-- Request Dialog -->
  <RequestDialog
    v-model="requestDialog"
    :candidate="selectedCandidate"
    :request-type="selectedType"
    @success="onRequestSuccess"
  />

  <!-- Snackbar -->
  <v-snackbar v-model="snackbar" color="success" timeout="3000" location="bottom">
    <v-icon icon="mdi-check-circle" class="mr-2" />
    {{ $t('actions.successMsg') }}
  </v-snackbar>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useDisplay } from 'vuetify'
import { useCandidateStore } from '@/stores/candidates'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppTopBar from '@/components/layout/AppTopBar.vue'
import CandidateCard from '@/components/CandidateCard.vue'
import FilterPanel from '@/components/FilterPanel.vue'
import RequestDialog from '@/components/RequestDialog.vue'
import api from '@/plugins/axios'

const store = useCandidateStore()
const { mobile } = useDisplay()
const isMobile = computed(() => mobile.value)

const drawer = ref(false)
const loading = computed(() => store.loading)
const currentPage = ref(1)
const searchInput = ref('')
const requestDialog = ref(false)
const selectedCandidate = ref(null)
const selectedType = ref('')
const snackbar = ref(false)
const myRequests = ref([]) // Track user's existing requests

onMounted(async () => {
  await Promise.all([
    store.fetchCandidates(1),
    store.fetchFilterOptions(),
    fetchMyRequests()
  ])
})

async function fetchMyRequests() {
  try {
    const res = await api.get('/requests')
    myRequests.value = res.data.data || []
  } catch {}
}

function getRequestedTypes(candidateId) {
  return myRequests.value
    .filter(r => r.candidate_id === candidateId && ['pending', 'processing'].includes(r.status))
    .map(r => r.request_type)
}

function updateFilters(newFilters) {
  Object.assign(store.filters, newFilters)
}

async function applyFilters() {
  store.filters.search = searchInput.value
  currentPage.value = 1
  await store.fetchCandidates(1)
}

async function resetFilters() {
  store.resetFilters()
  searchInput.value = ''
  currentPage.value = 1
  await store.fetchCandidates(1)
}

async function clearSearch() {
  searchInput.value = ''
  store.filters.search = ''
  await store.fetchCandidates(1)
}

async function changePage(page) {
  await store.fetchCandidates(page)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function openRequestDialog({ candidate, type }) {
  selectedCandidate.value = candidate
  selectedType.value = type
  requestDialog.value = true
}

function onRequestSuccess(type) {
  snackbar.value = true
  fetchMyRequests()
}
</script>
