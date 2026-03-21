import { defineStore } from 'pinia'
import { ref, reactive } from 'vue'
import api from '@/plugins/axios'

export const useCandidateStore = defineStore('candidates', () => {
  const candidates = ref([])
  const loading = ref(false)
  const pagination = reactive({ current_page: 1, last_page: 1, total: 0, per_page: 12 })
  const filters = reactive({
    search: '',
    occupation: [],
    german_level: [],
    nationality: [],
    country_of_origin: [],
    additional_language: '',
    age_min: null,
    age_max: null
  })
  const filterOptions = reactive({
    occupations: [],
    nationalities: [],
    german_levels: [],
    countries_of_origin: []
  })

  async function fetchCandidates(page = 1) {
    loading.value = true
    try {
      const params = { page }
      if (filters.search) params.search = filters.search
      if (filters.occupation.length) params['occupation[]'] = filters.occupation
      if (filters.german_level.length) params['german_level[]'] = filters.german_level
      if (filters.nationality.length) params['nationality[]'] = filters.nationality
      if (filters.country_of_origin.length) params['country_of_origin[]'] = filters.country_of_origin
      if (filters.additional_language) params.additional_language = filters.additional_language
      if (filters.age_min) params.age_min = filters.age_min
      if (filters.age_max) params.age_max = filters.age_max

      const res = await api.get('/candidates', { params })
      candidates.value = res.data.data
      pagination.current_page = res.data.current_page
      pagination.last_page = res.data.last_page
      pagination.total = res.data.total
      pagination.per_page = res.data.per_page
    } finally {
      loading.value = false
    }
  }

  async function fetchFilterOptions() {
    const res = await api.get('/candidates/filter-options')
    Object.assign(filterOptions, res.data)
  }

  function resetFilters() {
    filters.search = ''
    filters.occupation = []
    filters.german_level = []
    filters.nationality = []
    filters.country_of_origin = []
    filters.additional_language = ''
    filters.age_min = null
    filters.age_max = null
  }

  return {
    candidates, loading, pagination, filters, filterOptions,
    fetchCandidates, fetchFilterOptions, resetFilters
  }
})
