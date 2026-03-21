<template>
  <!-- Desktop: permanent panel -->
  <div v-if="!isMobile" class="filter-panel">
    <div class="filter-header d-flex align-center justify-space-between mb-4">
      <span class="text-subtitle-1 font-weight-bold">{{ $t('filters.title') }}</span>
      <v-chip v-if="activeCount > 0" size="small" color="primary" variant="flat">
        {{ $t('filters.activeFilters', { count: activeCount }) }}
      </v-chip>
    </div>

    <FilterForm :filters="filters" :options="options" @update="updateFilter" />

    <div class="d-flex ga-2 mt-4">
      <v-btn variant="outlined" @click="$emit('reset')" size="small">{{ $t('filters.reset') }}</v-btn>
      <v-btn color="primary" variant="flat" @click="$emit('apply')" size="small" class="flex-grow-1">
        {{ $t('filters.apply') }}
      </v-btn>
    </div>
  </div>

  <!-- Mobile: bottom sheet -->
  <v-bottom-sheet v-else v-model="sheetOpen">
    <template #activator="{ props: sheetProps }">
      <v-btn
        v-bind="sheetProps"
        color="primary"
        variant="outlined"
        prepend-icon="mdi-filter-variant"
        size="small"
      >
        {{ $t('filters.title') }}
        <v-badge v-if="activeCount > 0" :content="activeCount" color="primary" floating />
      </v-btn>
    </template>

    <v-card rounded="t-xl">
      <div class="drag-handle mx-auto mt-2" />
      <v-card-title class="d-flex align-center justify-space-between pt-4 px-4">
        {{ $t('filters.title') }}
        <v-btn icon="mdi-close" variant="text" size="small" @click="sheetOpen = false" />
      </v-card-title>
      <v-card-text class="px-4 pb-4" style="max-height: 70vh; overflow-y: auto;">
        <FilterForm :filters="filters" :options="options" @update="updateFilter" />
        <div class="d-flex ga-2 mt-4">
          <v-btn variant="outlined" @click="$emit('reset'); sheetOpen = false" block>{{ $t('filters.reset') }}</v-btn>
          <v-btn color="primary" variant="flat" @click="$emit('apply'); sheetOpen = false" block>
            {{ $t('filters.apply') }}
          </v-btn>
        </div>
      </v-card-text>
    </v-card>
  </v-bottom-sheet>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useDisplay } from 'vuetify'
import FilterForm from './FilterForm.vue'

const props = defineProps({
  filters: { type: Object, required: true },
  options: { type: Object, required: true }
})

const emit = defineEmits(['update:filters', 'apply', 'reset'])

const { mobile } = useDisplay()
const isMobile = computed(() => mobile.value)
const sheetOpen = ref(false)

const activeCount = computed(() => {
  let count = 0
  if (props.filters.search) count++
  if (props.filters.occupation?.length) count++
  if (props.filters.german_level?.length) count++
  if (props.filters.nationality?.length) count++
  if (props.filters.country_of_origin?.length) count++
  if (props.filters.additional_language) count++
  if (props.filters.age_min || props.filters.age_max) count++
  return count
})

function updateFilter(key, value) {
  emit('update:filters', { ...props.filters, [key]: value })
}
</script>

<style scoped>
.filter-panel {
  padding: 16px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
  position: sticky;
  top: 16px;
}
.drag-handle {
  width: 40px; height: 4px;
  background: #E0E0E0; border-radius: 2px;
}
</style>
