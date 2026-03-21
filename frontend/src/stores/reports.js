import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/plugins/axios'

export const useReportStore = defineStore('reports', () => {
  const candidateList = ref([])
  const requestStats = ref(null)
  const candidateProfile = ref(null)
  const loading = ref(false)
  const error = ref(null)

  async function fetchCandidateList(filters = {}) {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/reports/candidates', { params: filters })
      candidateList.value = res.data
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  async function fetchRequestStats() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/reports/requests-stats')
      requestStats.value = res.data
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  async function fetchCandidateProfile(id) {
    loading.value = true
    error.value = null
    try {
      const res = await api.get(`/reports/candidate-profile/${id}`)
      candidateProfile.value = res.data
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  return {
    candidateList, requestStats, candidateProfile, loading, error,
    fetchCandidateList, fetchRequestStats, fetchCandidateProfile
  }
})
