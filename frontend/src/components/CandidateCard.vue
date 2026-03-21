<template>
  <v-card class="candidate-card h-100" elevation="2">
    <v-card-text class="pb-0">
      <!-- Header row -->
      <div class="d-flex justify-space-between align-center mb-3">
        <v-chip size="small" variant="outlined" color="primary">
          #{{ String(candidate.id).padStart(3, '0') }}
        </v-chip>
        <v-chip size="small" :color="germanLevelColor" variant="flat">
          <span style="color:white">{{ candidate.german_level }}</span>
        </v-chip>
      </div>

      <!-- Avatar -->
      <div class="text-center mb-3">
        <v-avatar size="80" :color="candidate.image_path ? undefined : 'grey-lighten-2'">
          <v-img
            v-if="candidate.image_path"
            :src="`http://localhost:8000/storage/candidates/${candidate.image_path}`"
            cover
          />
          <v-icon v-else icon="mdi-account-circle" size="80" color="grey" />
        </v-avatar>
      </div>

      <!-- Info -->
      <div class="text-center">
        <div class="text-subtitle-1 font-weight-bold mb-1">
          {{ candidate.first_name }} {{ candidate.last_name }}
        </div>
        <div class="text-body-2 text-medium-emphasis mb-1">
          <v-icon size="14" class="mr-1">mdi-stethoscope</v-icon>
          {{ candidate.occupation }}
        </div>
        <div class="text-body-2 text-medium-emphasis mb-1">
          {{ candidate.nationality }} · {{ candidate.age }} yrs
        </div>
        <div class="text-body-2 text-medium-emphasis mb-2">
          <v-icon size="14" class="mr-1">mdi-earth</v-icon>
          {{ candidate.country_of_origin }}
        </div>

        <!-- Language chips -->
        <div class="d-flex flex-wrap justify-center mb-2" style="gap:4px">
          <v-chip
            v-for="lang in displayLanguages"
            :key="lang.language"
            size="x-small"
            variant="outlined"
            color="primary"
          >
            {{ lang.language }}: {{ lang.level }}
          </v-chip>
        </div>
      </div>
    </v-card-text>

    <v-divider class="mt-1" />

    <v-card-actions class="pa-2">
      <v-row no-gutters>
        <v-col cols="4" class="pa-1">
          <v-btn
            size="small"
            variant="outlined"
            color="secondary"
            block
            :disabled="isRequested('reserve')"
            @click="$emit('request', { candidate, type: 'reserve' })"
          >
            <v-icon size="14" class="mr-1">{{ isRequested('reserve') ? 'mdi-check' : 'mdi-bookmark' }}</v-icon>
            <span class="text-caption">{{ $t('actions.reserve') }}</span>
          </v-btn>
        </v-col>
        <v-col cols="4" class="pa-1">
          <v-btn
            size="small"
            variant="outlined"
            color="primary"
            block
            :disabled="isRequested('more_info')"
            @click="$emit('request', { candidate, type: 'more_info' })"
          >
            <v-icon size="14" class="mr-1">{{ isRequested('more_info') ? 'mdi-check' : 'mdi-information' }}</v-icon>
            <span class="text-caption">{{ $t('actions.moreInfo') }}</span>
          </v-btn>
        </v-col>
        <v-col cols="4" class="pa-1">
          <v-btn
            size="small"
            variant="outlined"
            color="deep-purple"
            block
            :disabled="isRequested('interview')"
            @click="$emit('request', { candidate, type: 'interview' })"
          >
            <v-icon size="14" class="mr-1">{{ isRequested('interview') ? 'mdi-check' : 'mdi-calendar-account' }}</v-icon>
            <span class="text-caption">{{ $t('actions.interview') }}</span>
          </v-btn>
        </v-col>
      </v-row>
    </v-card-actions>
  </v-card>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  candidate: { type: Object, required: true },
  requestedTypes: { type: Array, default: () => [] }
})

defineEmits(['request'])

const germanLevelColor = computed(() => {
  const level = props.candidate.german_level
  if (['C1', 'C2', 'Native'].includes(level)) return '#2E7D32'
  if (['B1', 'B2'].includes(level)) return '#1565C0'
  if (['A1', 'A2'].includes(level)) return '#E65100'
  return 'grey'
})

const displayLanguages = computed(() => {
  const langs = props.candidate.additional_languages || []
  return Array.isArray(langs) ? langs.slice(0, 3) : []
})

function isRequested(type) {
  return props.requestedTypes.includes(type)
}
</script>

<style scoped>
.candidate-card { transition: transform 0.2s, box-shadow 0.2s; }
.candidate-card:hover { transform: translateY(-2px); }
</style>
