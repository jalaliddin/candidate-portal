<template>
  <div class="profile-card">
    <v-card elevation="2" class="mb-4">
      <v-card-text class="text-center pa-5">
        <!-- Photo -->
        <v-avatar size="180" color="grey-lighten-2" class="mb-4">
          <v-img
            v-if="candidate.image_path"
            :src="`${$storageUrl}/candidates/${candidate.image_path}`"
            cover
          />
          <v-icon v-else icon="mdi-account-circle" size="180" color="grey" />
        </v-avatar>

        <!-- ID & Status -->
        <div class="d-flex justify-center align-center ga-2 mb-2">
          <v-chip color="primary" variant="outlined" size="small">
            #{{ String(candidate.id).padStart(3, '0') }}
          </v-chip>
          <v-chip
            v-if="isAdmin"
            :color="statusColor"
            variant="flat"
            size="small"
          >
            <span style="color:white">{{ candidate.status }}</span>
          </v-chip>
        </div>

        <div class="text-h6 font-weight-bold mb-1">
          {{ candidate.first_name }} {{ candidate.last_name }}
        </div>
        <div class="text-body-2 text-medium-emphasis mb-3">
          <v-icon size="14" class="mr-1">mdi-stethoscope</v-icon>
          {{ candidate.occupation }}
        </div>

        <v-divider class="mb-3" />

        <!-- Action buttons for clients -->
        <div v-if="!isAdmin && showActions" class="d-flex flex-column ga-2">
          <v-btn
            variant="outlined"
            color="secondary"
            prepend-icon="mdi-bookmark"
            :disabled="requestedTypes.includes('reserve')"
            @click="$emit('request', { candidate, type: 'reserve' })"
          >{{ $t('actions.reserve') }}</v-btn>
          <v-btn
            variant="outlined"
            color="primary"
            prepend-icon="mdi-information"
            :disabled="requestedTypes.includes('more_info')"
            @click="$emit('request', { candidate, type: 'more_info' })"
          >{{ $t('actions.moreInfo') }}</v-btn>
          <v-btn
            variant="outlined"
            color="deep-purple"
            prepend-icon="mdi-calendar-account"
            :disabled="requestedTypes.includes('interview')"
            @click="$emit('request', { candidate, type: 'interview' })"
          >{{ $t('actions.interview') }}</v-btn>
        </div>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  candidate: { type: Object, required: true },
  requestedTypes: { type: Array, default: () => [] },
  showActions: { type: Boolean, default: true }
})

defineEmits(['request'])
const authStore = useAuthStore()
const isAdmin = computed(() => authStore.isAdmin)

const statusColor = computed(() => ({
  Active: 'success',
  Inactive: 'warning',
  Placed: 'primary'
}[props.candidate.status] || 'grey'))
</script>
