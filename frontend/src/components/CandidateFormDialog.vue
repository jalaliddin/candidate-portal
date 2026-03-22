<template>
  <v-dialog v-model="dialog" max-width="800" scrollable>
    <v-card>
      <v-card-title class="d-flex align-center justify-space-between pa-5">
        <span class="text-h6">{{ isEdit ? $t('admin.editCandidate') : $t('admin.addCandidate') }}</span>
        <v-btn icon="mdi-close" variant="text" @click="dialog = false" />
      </v-card-title>

      <v-divider />

      <v-card-text class="pa-5">
        <v-row>
          <!-- Left: Image upload -->
          <v-col cols="12" md="4">
            <div
              class="image-upload-area"
              :class="{ 'drag-over': isDragging }"
              @click="triggerFileInput"
              @dragover.prevent="isDragging = true"
              @dragleave="isDragging = false"
              @drop.prevent="handleDrop"
            >
              <v-img
                v-if="imagePreview"
                :src="imagePreview"
                cover
                class="rounded-lg"
                height="150"
              />
              <div v-else class="upload-placeholder">
                <v-icon size="48" color="grey">mdi-camera-plus</v-icon>
                <div class="text-body-2 text-medium-emphasis mt-2">
                  Drag & drop or click to upload
                </div>
                <div class="text-caption text-medium-emphasis">JPG, PNG · max 2MB</div>
              </div>
            </div>
            <input ref="fileInput" type="file" accept="image/jpg,image/jpeg,image/png" class="d-none" @change="handleFileChange" />
          </v-col>

          <!-- Right: Fields -->
          <v-col cols="12" md="8">
            <v-row dense>
              <v-col cols="6">
                <v-text-field
                  v-model="form.first_name"
                  label="First Name *"
                  variant="outlined"
                  density="compact"
                  :error-messages="errors.first_name"
                />
              </v-col>
              <v-col cols="6">
                <v-text-field
                  v-model="form.last_name"
                  label="Last Name *"
                  variant="outlined"
                  density="compact"
                  :error-messages="errors.last_name"
                />
              </v-col>
              <v-col cols="6">
                <v-text-field
                  v-model="form.nationality"
                  label="Nationality *"
                  variant="outlined"
                  density="compact"
                />
              </v-col>
              <v-col cols="6">
                <v-text-field
                  v-model="form.date_of_birth"
                  label="Date of Birth *"
                  type="date"
                  variant="outlined"
                  density="compact"
                />
              </v-col>
              <v-col cols="6">
                <v-text-field
                  v-model="form.place_of_birth"
                  label="Place of Birth"
                  variant="outlined"
                  density="compact"
                />
              </v-col>
              <v-col cols="6">
                <v-autocomplete
                  v-model="form.country_of_origin"
                  :items="COUNTRIES"
                  label="Country of Origin *"
                  variant="outlined"
                  density="compact"
                  clearable
                  :error-messages="errors.country_of_origin"
                />
              </v-col>
            </v-row>
          </v-col>

          <!-- Full-width fields -->
          <v-col cols="12" md="4">
            <v-select
              v-model="form.german_level"
              :items="germanLevels"
              label="German Level *"
              variant="outlined"
              density="compact"
            />
          </v-col>
          <v-col cols="12" md="4">
            <v-autocomplete
              v-model="form.occupation"
              :items="occupationNames"
              label="Occupation *"
              variant="outlined"
              density="compact"
              clearable
              :loading="occupationsLoading"
              :error-messages="errors.occupation"
            />
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="form.status"
              :items="statusItems"
              label="Status *"
              variant="outlined"
              density="compact"
            />
          </v-col>

          <!-- Additional Languages -->
          <v-col cols="12">
            <div class="d-flex align-center justify-space-between mb-2">
              <span class="text-body-2 font-weight-medium">Additional Languages</span>
              <v-btn size="small" prepend-icon="mdi-plus" variant="outlined" @click="addLanguage">
                Add Language
              </v-btn>
            </div>
            <div v-for="(lang, i) in form.additional_languages" :key="i" class="d-flex ga-2 mb-2">
              <v-text-field
                v-model="lang.language"
                label="Language"
                variant="outlined"
                density="compact"
                hide-details
              />
              <v-select
                v-model="lang.level"
                :items="languageLevels"
                label="Level"
                variant="outlined"
                density="compact"
                hide-details
                style="max-width: 100px"
              />
              <v-btn icon="mdi-delete" variant="text" color="error" size="small" @click="removeLanguage(i)" />
            </div>
          </v-col>

          <v-col cols="12">
            <v-textarea
              v-model="form.certificate"
              label="Certificate"
              variant="outlined"
              density="compact"
              rows="2"
              hide-details
            />
          </v-col>
          <v-col cols="12">
            <v-textarea
              v-model="form.expose_university_degree"
              label="University Degree"
              variant="outlined"
              density="compact"
              rows="2"
              hide-details
            />
          </v-col>
          <v-col cols="12">
            <v-text-field
              v-model="form.anabin_status"
              label="Anabin Status"
              variant="outlined"
              density="compact"
              hide-details
            />
          </v-col>
        </v-row>
      </v-card-text>

      <v-divider />

      <v-card-actions class="pa-4">
        <v-spacer />
        <v-btn variant="text" @click="dialog = false">{{ $t('common.cancel') }}</v-btn>
        <v-btn color="primary" variant="flat" :loading="loading" @click="save">
          {{ $t('common.save') }} Candidate
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import api from '@/plugins/axios'
import { COUNTRIES } from '@/composables/useCountries'

const props = defineProps({
  modelValue: Boolean,
  candidate: { type: Object, default: null }
})
const emit = defineEmits(['update:modelValue', 'saved'])

const dialog = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v)
})

const isEdit = computed(() => !!props.candidate)
const loading = ref(false)
const errors = ref({})
const fileInput = ref(null)
const imagePreview = ref(null)
const imageFile = ref(null)
const isDragging = ref(false)

const germanLevels = ['None', 'A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'Native']
const statusItems = ['Active', 'Inactive', 'Placed']
const languageLevels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'Native']

const occupations = ref([])
const occupationsLoading = ref(false)
const occupationNames = computed(() => occupations.value.map(o => o.name))

onMounted(async () => {
  occupationsLoading.value = true
  try {
    const { data } = await api.get('/occupations')
    occupations.value = data
  } finally {
    occupationsLoading.value = false
  }
})

const defaultForm = () => ({
  first_name: '',
  last_name: '',
  nationality: '',
  date_of_birth: '',
  place_of_birth: '',
  country_of_origin: '',
  german_level: 'None',
  occupation: '',
  status: 'Active',
  certificate: '',
  expose_university_degree: '',
  anabin_status: '',
  additional_languages: []
})

const form = ref(defaultForm())

watch(() => props.candidate, (val) => {
  if (val) {
    form.value = {
      first_name: val.first_name || '',
      last_name: val.last_name || '',
      nationality: val.nationality || '',
      date_of_birth: val.date_of_birth ? val.date_of_birth.slice(0, 10) : '',
      place_of_birth: val.place_of_birth || '',
      country_of_origin: val.country_of_origin || '',
      german_level: val.german_level || 'None',
      occupation: val.occupation || '',
      status: val.status || 'Active',
      certificate: val.certificate || '',
      expose_university_degree: val.expose_university_degree || '',
      anabin_status: val.anabin_status || '',
      additional_languages: val.additional_languages ? JSON.parse(JSON.stringify(val.additional_languages)) : []
    }
    const storageUrl = import.meta.env.VITE_STORAGE_URL || 'http://localhost:8000/storage'
    imagePreview.value = val.image_path ? `${storageUrl}/candidates/${val.image_path}` : null
  } else {
    form.value = defaultForm()
    imagePreview.value = null
    imageFile.value = null
  }
  errors.value = {}
}, { immediate: true })

function triggerFileInput() { fileInput.value?.click() }

function handleFileChange(e) {
  const file = e.target.files[0]
  if (file) processFile(file)
}

function handleDrop(e) {
  isDragging.value = false
  const file = e.dataTransfer.files[0]
  if (file) processFile(file)
}

function processFile(file) {
  if (file.size > 2 * 1024 * 1024) return alert('Image must be under 2MB')
  imageFile.value = file
  imagePreview.value = URL.createObjectURL(file)
}

function addLanguage() {
  form.value.additional_languages.push({ language: '', level: 'B1' })
}

function removeLanguage(i) {
  form.value.additional_languages.splice(i, 1)
}

async function save() {
  errors.value = {}
  if (!form.value.first_name) { errors.value.first_name = 'Required'; return }
  if (!form.value.last_name) { errors.value.last_name = 'Required'; return }
  if (!form.value.occupation) { errors.value.occupation = 'Required'; return }

  loading.value = true
  try {
    const fd = new FormData()
    Object.entries(form.value).forEach(([k, v]) => {
      if (k === 'additional_languages') {
        fd.append(k, JSON.stringify(v))
      } else if (v !== null && v !== undefined) {
        fd.append(k, v)
      }
    })
    if (imageFile.value) fd.append('image', imageFile.value)

    if (isEdit.value) {
      fd.append('_method', 'PUT')
      await api.post(`/admin/candidates/${props.candidate.id}`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      await api.post('/admin/candidates', fd, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }

    dialog.value = false
    emit('saved')
  } catch (e) {
    if (e.response?.data?.errors) errors.value = e.response.data.errors
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.image-upload-area {
  border: 2px dashed #E0E0E0;
  border-radius: 12px;
  min-height: 160px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: border-color 0.2s;
  overflow: hidden;
}
.image-upload-area:hover, .image-upload-area.drag-over {
  border-color: #1565C0;
}
.upload-placeholder {
  text-align: center;
  padding: 16px;
}
</style>
