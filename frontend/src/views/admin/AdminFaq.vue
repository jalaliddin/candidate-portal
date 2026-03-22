<template>
  <div>
    <AppTopBar :title="$t('admin.faqTitle')" @toggle-drawer="$emit('toggle-drawer')">
      <template #append>
        <v-btn
          color="primary"
          variant="flat"
          prepend-icon="mdi-plus"
          class="mr-3"
          @click="openCreate"
        >
          {{ $t('admin.addFaq') }}
        </v-btn>
      </template>
    </AppTopBar>

    <v-container fluid class="pa-6">
      <!-- Telegram Setup Info -->
      <v-alert
        type="info"
        variant="tonal"
        icon="mdi-robot"
        class="mb-4"
        closable
      >
        <strong>Telegram Bot Setup:</strong>
        After adding FAQs, register the webhook:
        <code class="ml-1">GET https://api.telegram.org/bot<b>{TOKEN}</b>/setWebhook?url=https://de.eversoft.uz/api/telegram/webhook</code>
      </v-alert>

      <v-card elevation="2">
        <v-data-table
          :headers="headers"
          :items="faqs"
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

          <template #item.answer="{ item }">
            <span class="text-truncate d-inline-block" style="max-width:300px">{{ item.answer }}</span>
          </template>

          <template #item.category="{ item }">
            <v-chip v-if="item.category" size="small" color="primary" variant="tonal">{{ item.category }}</v-chip>
            <span v-else class="text-disabled">—</span>
          </template>

          <template #item.actions="{ item }">
            <v-btn icon="mdi-pencil" variant="text" size="small" color="primary" @click="openEdit(item)" />
            <v-btn icon="mdi-delete" variant="text" size="small" color="error" @click="confirmDelete(item)" />
          </template>
        </v-data-table>
      </v-card>
    </v-container>

    <!-- Add/Edit Dialog -->
    <v-dialog v-model="formDialog" max-width="600">
      <v-card>
        <v-card-title class="pa-5">
          {{ editingFaq ? $t('admin.editFaq') : $t('admin.addFaq') }}
        </v-card-title>
        <v-card-text class="px-5">
          <v-text-field
            v-model="form.question"
            :label="$t('admin.faqQuestion') + ' *'"
            variant="outlined"
            density="compact"
            class="mb-3"
          />
          <v-textarea
            v-model="form.answer"
            :label="$t('admin.faqAnswer') + ' *'"
            variant="outlined"
            density="compact"
            rows="4"
            class="mb-3"
          />
          <v-row>
            <v-col cols="8">
              <v-text-field
                v-model="form.category"
                :label="$t('admin.faqCategory')"
                variant="outlined"
                density="compact"
                placeholder="e.g. Visa, Documents, Process"
              />
            </v-col>
            <v-col cols="4">
              <v-text-field
                v-model.number="form.sort_order"
                :label="$t('admin.faqSortOrder')"
                variant="outlined"
                density="compact"
                type="number"
                min="0"
              />
            </v-col>
          </v-row>
          <v-switch
            v-model="form.is_active"
            label="Active"
            color="success"
            hide-details
            density="compact"
          />
        </v-card-text>
        <v-card-actions class="px-5 pb-4">
          <v-spacer />
          <v-btn variant="text" @click="formDialog = false">{{ $t('common.cancel') }}</v-btn>
          <v-btn color="primary" variant="flat" :loading="saving" @click="saveFaq">{{ $t('common.save') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirm -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title class="pa-5">{{ $t('common.confirm') }}</v-card-title>
        <v-card-text class="px-5">{{ $t('admin.confirmDeleteFaq') }}</v-card-text>
        <v-card-actions class="px-5 pb-4">
          <v-spacer />
          <v-btn variant="text" @click="deleteDialog = false">{{ $t('common.cancel') }}</v-btn>
          <v-btn color="error" variant="flat" :loading="deleting" @click="deleteFaq">{{ $t('common.delete') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/plugins/axios'
import AppTopBar from '@/components/layout/AppTopBar.vue'

const emit = defineEmits(['toggle-drawer'])
const { t } = useI18n()

const faqs = ref([])
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const formDialog = ref(false)
const deleteDialog = ref(false)
const editingFaq = ref(null)
const deletingFaq = ref(null)

const emptyForm = () => ({ question: '', answer: '', category: '', sort_order: 0, is_active: true })
const form = ref(emptyForm())

const headers = [
  { title: '#', key: 'id', width: 60 },
  { title: t('admin.faqQuestion'), key: 'question' },
  { title: t('admin.faqAnswer'), key: 'answer' },
  { title: t('admin.faqCategory'), key: 'category', width: 130 },
  { title: t('admin.faqSortOrder'), key: 'sort_order', width: 100 },
  { title: 'Active', key: 'is_active', width: 90 },
  { title: t('common.actions'), key: 'actions', sortable: false, width: 100 },
]

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/faqs')
    faqs.value = data
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingFaq.value = null
  form.value = emptyForm()
  formDialog.value = true
}

function openEdit(faq) {
  editingFaq.value = faq
  form.value = { ...faq }
  formDialog.value = true
}

async function saveFaq() {
  if (!form.value.question || !form.value.answer) return
  saving.value = true
  try {
    if (editingFaq.value) {
      const { data } = await api.put(`/admin/faqs/${editingFaq.value.id}`, form.value)
      const idx = faqs.value.findIndex(f => f.id === data.id)
      if (idx !== -1) faqs.value[idx] = data
    } else {
      const { data } = await api.post('/admin/faqs', form.value)
      faqs.value.push(data)
    }
    formDialog.value = false
  } finally {
    saving.value = false
  }
}

function confirmDelete(faq) {
  deletingFaq.value = faq
  deleteDialog.value = true
}

async function deleteFaq() {
  deleting.value = true
  try {
    await api.delete(`/admin/faqs/${deletingFaq.value.id}`)
    faqs.value = faqs.value.filter(f => f.id !== deletingFaq.value.id)
    deleteDialog.value = false
  } finally {
    deleting.value = false
  }
}

async function toggleActive(faq) {
  await api.put(`/admin/faqs/${faq.id}`, { ...faq })
}

onMounted(load)
</script>
