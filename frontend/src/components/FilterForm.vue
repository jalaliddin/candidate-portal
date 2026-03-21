<template>
  <div class="filter-form">
    <v-autocomplete
      :model-value="filters.occupation"
      :items="options.occupations || []"
      :label="$t('filters.occupation')"
      multiple
      chips
      closable-chips
      density="compact"
      variant="outlined"
      class="mb-3"
      hide-details
      @update:model-value="$emit('update', 'occupation', $event)"
    />

    <v-select
      :model-value="filters.german_level"
      :items="germanLevelItems"
      :label="$t('filters.germanLevel')"
      multiple
      chips
      closable-chips
      density="compact"
      variant="outlined"
      class="mb-3"
      hide-details
      @update:model-value="$emit('update', 'german_level', $event)"
    />

    <div class="text-caption text-medium-emphasis mb-1">{{ $t('filters.ageRange') }}</div>
    <div class="d-flex ga-2 mb-3">
      <v-text-field
        :model-value="filters.age_min"
        :label="$t('common.from')"
        type="number"
        density="compact"
        variant="outlined"
        hide-details
        style="max-width: 80px"
        @update:model-value="$emit('update', 'age_min', $event || null)"
      />
      <v-text-field
        :model-value="filters.age_max"
        :label="$t('common.to')"
        type="number"
        density="compact"
        variant="outlined"
        hide-details
        style="max-width: 80px"
        @update:model-value="$emit('update', 'age_max', $event || null)"
      />
    </div>

    <v-autocomplete
      :model-value="filters.nationality"
      :items="options.nationalities || []"
      :label="$t('filters.nationality')"
      multiple
      chips
      closable-chips
      density="compact"
      variant="outlined"
      class="mb-3"
      hide-details
      @update:model-value="$emit('update', 'nationality', $event)"
    />

    <v-autocomplete
      :model-value="filters.country_of_origin"
      :items="options.countries_of_origin || []"
      :label="$t('filters.countryOfOrigin')"
      multiple
      chips
      closable-chips
      density="compact"
      variant="outlined"
      class="mb-3"
      hide-details
      @update:model-value="$emit('update', 'country_of_origin', $event)"
    />

    <v-text-field
      :model-value="filters.additional_language"
      :label="$t('filters.additionalLanguage')"
      density="compact"
      variant="outlined"
      hide-details
      clearable
      @update:model-value="$emit('update', 'additional_language', $event)"
    />
  </div>
</template>

<script setup>
const props = defineProps({
  filters: Object,
  options: Object
})
defineEmits(['update'])

const germanLevelItems = ['None', 'A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'Native']
</script>
