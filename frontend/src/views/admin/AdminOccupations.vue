<template>
  <v-container fluid class="pa-6">
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h1 class="text-h5 font-weight-bold">Occupations</h1>
        <p class="text-medium-emphasis text-body-2 mt-1">Manage occupations list for candidate profiles</p>
      </div>
      <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreate">Add Occupation</v-btn>
    </div>

    <v-card>
      <v-card-text class="pa-0">
        <v-data-table
          :headers="headers"
          :items="items"
          :loading="loading"
          :search="search"
          items-per-page="20"
        >
          <template #top>
            <div class="pa-4">
              <v-text-field
                v-model="search"
                prepend-inner-icon="mdi-magnify"
                placeholder="Search occupations..."
                variant="outlined"
                density="compact"
                hide-details
                style="max-width: 320px"
              />
            </div>
          </template>

          <template #item.is_active="{ item }">
            <v-chip :color="item.is_active ? 'success' : 'error'" size="small" variant="tonal">
              {{ item.is_active ? 'Active' : 'Inactive' }}
            </v-chip>
          </template>

          <template #item.actions="{ item }">
            <v-btn icon="mdi-pencil" variant="text" size="small" @click="openEdit(item)" />
            <v-btn icon="mdi-delete" variant="text" size="small" color="error" @click="remove(item)" />
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>

    <!-- Form Dialog -->
    <v-dialog v-model="dialog" max-width="420">
      <v-card>
        <v-card-title class="d-flex align-center justify-space-between pa-5">
          <span class="text-h6">{{ editItem ? 'Edit Occupation' : 'Add Occupation' }}</span>
          <v-btn icon="mdi-close" variant="text" @click="dialog = false" />
        </v-card-title>
        <v-divider />
        <v-card-text class="pa-5">
          <v-text-field
            v-model="form.name"
            label="Name *"
            variant="outlined"
            density="compact"
            :error-messages="errors.name"
            class="mb-3"
          />
          <v-text-field
            v-model.number="form.sort_order"
            label="Sort Order"
            type="number"
            variant="outlined"
            density="compact"
            hint="Lower = appears first"
            persistent-hint
            class="mb-3"
          />
          <v-switch
            v-model="form.is_active"
            label="Active"
            color="primary"
            hide-details
          />
        </v-card-text>
        <v-divider />
        <v-card-actions class="pa-4">
          <v-spacer />
          <v-btn variant="text" @click="dialog = false">Cancel</v-btn>
          <v-btn color="primary" variant="flat" :loading="saving" @click="save">Save</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/plugins/axios'

const loading = ref(false)
const saving = ref(false)
const dialog = ref(false)
const search = ref('')
const items = ref([])
const editItem = ref(null)
const errors = ref({})

const headers = [
  { title: 'Name', key: 'name', sortable: true },
  { title: 'Sort Order', key: 'sort_order', sortable: true, width: '120px' },
  { title: 'Status', key: 'is_active', sortable: true, width: '100px' },
  { title: 'Actions', key: 'actions', sortable: false, width: '100px', align: 'end' },
]

const defaultForm = () => ({ name: '', is_active: true, sort_order: 0 })
const form = ref(defaultForm())

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/occupations', { params: { per_page: 100 } })
    items.value = data.data ?? data
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editItem.value = null
  form.value = defaultForm()
  errors.value = {}
  dialog.value = true
}

function openEdit(item) {
  editItem.value = item
  form.value = { name: item.name, is_active: item.is_active, sort_order: item.sort_order }
  errors.value = {}
  dialog.value = true
}

async function save() {
  errors.value = {}
  saving.value = true
  try {
    if (editItem.value) {
      await api.put(`/admin/occupations/${editItem.value.id}`, form.value)
    } else {
      await api.post('/admin/occupations', form.value)
    }
    dialog.value = false
    await load()
  } catch (e) {
    if (e.response?.data?.errors) errors.value = e.response.data.errors
  } finally {
    saving.value = false
  }
}

async function remove(item) {
  if (!confirm(`Delete "${item.name}"?`)) return
  await api.delete(`/admin/occupations/${item.id}`)
  await load()
}

onMounted(load)
</script>
