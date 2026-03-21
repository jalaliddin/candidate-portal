<template>
  <div>
    <AppTopBar :title="$t('nav.adminUsers')" @toggle-drawer="$emit('toggle-drawer')">
      <template #append>
        <v-btn
          color="primary"
          variant="flat"
          prepend-icon="mdi-plus"
          class="mr-3"
          @click="openCreate"
        >
          {{ $t('admin.addUser') }}
        </v-btn>
      </template>
    </AppTopBar>

    <v-container fluid class="pa-6">
      <v-card elevation="2">
        <v-data-table
          :headers="headers"
          :items="users"
          :loading="loading"
          hover
        >
          <template #item.is_active="{ item }">
            <v-switch
              v-model="item.is_active"
              color="success"
              hide-details
              density="compact"
              @update:model-value="toggleActive(item)"
            />
          </template>

          <template #item.candidate_requests_count="{ item }">
            <v-chip size="small" color="primary" variant="tonal">
              {{ item.candidate_requests_count || 0 }}
            </v-chip>
          </template>

          <template #item.actions="{ item }">
            <v-btn icon="mdi-pencil" variant="text" size="small" color="primary" @click="openEdit(item)" />
            <v-btn
              icon="mdi-account-off"
              variant="text"
              size="small"
              color="error"
              :disabled="!item.is_active"
              @click="deactivate(item)"
            />
          </template>
        </v-data-table>
      </v-card>
    </v-container>

    <!-- Add/Edit Dialog -->
    <v-dialog v-model="formDialog" max-width="500">
      <v-card>
        <v-card-title class="pa-5">
          {{ editingUser ? $t('admin.editUser') : $t('admin.addUser') }}
        </v-card-title>
        <v-card-text class="px-5">
          <v-text-field v-model="form.name" label="Name *" variant="outlined" density="compact" class="mb-3" />
          <v-text-field v-model="form.email" label="Email *" type="email" variant="outlined" density="compact" class="mb-3" />
          <v-text-field v-model="form.company_name" label="Company Name" variant="outlined" density="compact" class="mb-3" />
          <v-text-field v-model="form.country" label="Country" variant="outlined" density="compact" />
        </v-card-text>
        <v-card-actions class="px-5 pb-4">
          <v-spacer />
          <v-btn variant="text" @click="formDialog = false">Cancel</v-btn>
          <v-btn color="primary" variant="flat" :loading="saving" @click="saveUser">Save</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Generated Password Dialog -->
    <v-dialog v-model="passwordDialog" max-width="400">
      <v-card>
        <v-card-title class="pa-5">{{ $t('admin.generatedPassword') }}</v-card-title>
        <v-card-text class="px-5">
          <div class="d-flex align-center ga-2 pa-3 rounded-lg" style="background:#F0F4F8">
            <code class="text-h6 flex-grow-1">{{ generatedPassword }}</code>
            <v-btn icon="mdi-content-copy" size="small" variant="text" @click="copyPassword" />
          </div>
          <div class="text-caption text-medium-emphasis mt-2">Share this password securely. It won't be shown again.</div>
        </v-card-text>
        <v-card-actions class="px-5 pb-4">
          <v-spacer />
          <v-btn color="primary" variant="flat" @click="passwordDialog = false">{{ $t('common.close') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-snackbar v-model="snackbar" :color="snackbarColor" timeout="3000">{{ snackbarMsg }}</v-snackbar>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppTopBar from '@/components/layout/AppTopBar.vue'
import api from '@/plugins/axios'

defineEmits(['toggle-drawer'])

const users = ref([])
const loading = ref(false)
const saving = ref(false)
const formDialog = ref(false)
const passwordDialog = ref(false)
const editingUser = ref(null)
const generatedPassword = ref('')
const snackbar = ref(false)
const snackbarMsg = ref('')
const snackbarColor = ref('success')

const form = ref({ name: '', email: '', company_name: '', country: '' })

const headers = [
  { title: 'Name', key: 'name', sortable: true },
  { title: 'Email', key: 'email', sortable: true },
  { title: 'Company', key: 'company_name', sortable: true },
  { title: 'Country', key: 'country', sortable: true },
  { title: 'Active', key: 'is_active', sortable: false, width: 90 },
  { title: 'Requests', key: 'candidate_requests_count', sortable: true, width: 100 },
  { title: 'Actions', key: 'actions', sortable: false, width: 100 },
]

onMounted(fetchUsers)

async function fetchUsers() {
  loading.value = true
  try {
    const res = await api.get('/admin/users')
    users.value = res.data.data || res.data
  } finally { loading.value = false }
}

function openCreate() {
  editingUser.value = null
  form.value = { name: '', email: '', company_name: '', country: '' }
  formDialog.value = true
}

function openEdit(user) {
  editingUser.value = user
  form.value = { name: user.name, email: user.email, company_name: user.company_name || '', country: user.country || '' }
  formDialog.value = true
}

async function saveUser() {
  if (!form.value.name || !form.value.email) return
  saving.value = true
  try {
    if (editingUser.value) {
      await api.patch(`/admin/users/${editingUser.value.id}`, form.value)
      formDialog.value = false
      showSnackbar('Client updated')
      fetchUsers()
    } else {
      const res = await api.post('/admin/users', form.value)
      generatedPassword.value = res.data.generated_password
      formDialog.value = false
      passwordDialog.value = true
      fetchUsers()
    }
  } catch (e) {
    showSnackbar(e.response?.data?.message || 'Error saving user', 'error')
  } finally {
    saving.value = false
  }
}

async function toggleActive(user) {
  try {
    await api.patch(`/admin/users/${user.id}`, { is_active: user.is_active })
    showSnackbar(`User ${user.is_active ? 'activated' : 'deactivated'}`)
  } catch {
    user.is_active = !user.is_active
    showSnackbar('Error updating user', 'error')
  }
}

async function deactivate(user) {
  try {
    await api.delete(`/admin/users/${user.id}`)
    showSnackbar('User deactivated')
    fetchUsers()
  } catch {
    showSnackbar('Error', 'error')
  }
}

function copyPassword() {
  navigator.clipboard.writeText(generatedPassword.value)
  showSnackbar('Password copied!')
}

function showSnackbar(msg, color = 'success') {
  snackbarMsg.value = msg
  snackbarColor.value = color
  snackbar.value = true
}
</script>
