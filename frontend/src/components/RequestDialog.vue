<template>
  <v-dialog v-model="dialog" max-width="500">
    <v-card>
      <v-card-title class="d-flex align-center ga-2 pt-4 px-6">
        <v-icon :icon="typeIcon" :color="typeColor" size="24" />
        <span class="text-h6">{{ typeTitle }}</span>
      </v-card-title>

      <v-card-text class="px-6 pb-0">
        <div class="mb-4 pa-3 rounded-lg" style="background:#F0F4F8">
          <div class="text-body-2 text-medium-emphasis mb-1">{{ $t('requests.candidate') }}</div>
          <div class="d-flex align-center ga-2">
            <strong>{{ candidate?.first_name }} {{ candidate?.last_name }}</strong>
            <v-chip size="x-small" color="primary" variant="outlined">
              #{{ String(candidate?.id || 0).padStart(3, '0') }}
            </v-chip>
          </div>
          <div class="text-body-2 text-medium-emphasis mt-1">{{ candidate?.occupation }}</div>
        </div>

        <v-textarea
          v-model="message"
          :label="$t('actions.message')"
          rows="3"
          variant="outlined"
          hide-details
          class="mb-2"
        />
      </v-card-text>

      <v-card-actions class="px-6 pb-4 pt-3">
        <v-spacer />
        <v-btn variant="text" @click="dialog = false">{{ $t('actions.cancel') }}</v-btn>
        <v-btn
          :color="typeColor"
          variant="flat"
          :loading="loading"
          append-icon="mdi-arrow-right"
          @click="submit"
        >
          {{ $t('actions.submit') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/plugins/axios'

const { t } = useI18n()
const props = defineProps({
  modelValue: Boolean,
  candidate: Object,
  requestType: String
})
const emit = defineEmits(['update:modelValue', 'success'])

const dialog = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v)
})

const message = ref('')
const loading = ref(false)

const typeIcon = computed(() => ({
  reserve: 'mdi-bookmark',
  more_info: 'mdi-information',
  interview: 'mdi-calendar-account'
}[props.requestType] || 'mdi-bookmark'))

const typeColor = computed(() => ({
  reserve: 'secondary',
  more_info: 'primary',
  interview: 'deep-purple'
}[props.requestType] || 'primary'))

const typeTitle = computed(() => ({
  reserve: t('actions.reserveTitle'),
  more_info: t('actions.moreInfoTitle'),
  interview: t('actions.interviewTitle')
}[props.requestType] || ''))

async function submit() {
  if (!props.candidate || !props.requestType) return
  loading.value = true
  try {
    await api.post('/requests', {
      candidate_id: props.candidate.id,
      request_type: props.requestType,
      message: message.value || undefined
    })
    message.value = ''
    dialog.value = false
    emit('success', props.requestType)
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}
</script>
