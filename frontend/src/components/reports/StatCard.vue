<template>
  <v-card class="stat-card" elevation="2">
    <v-card-text class="pa-5">
      <div class="d-flex align-center justify-space-between">
        <div>
          <div class="stat-number">{{ value }}</div>
          <div class="text-body-2 text-medium-emphasis mt-1">{{ label }}</div>
          <div v-if="trend" class="text-caption mt-1" :class="trendPositive ? 'text-success' : 'text-error'">
            <v-icon size="14">{{ trendPositive ? 'mdi-trending-up' : 'mdi-trending-down' }}</v-icon>
            {{ trend }}
          </div>
        </div>
        <div class="stat-icon-bg" :style="{ background: iconBg }">
          <v-icon :icon="icon" :color="iconColor" size="28" />
        </div>
      </div>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  value: { type: [Number, String], default: 0 },
  label: { type: String, default: '' },
  icon: { type: String, default: 'mdi-chart-box' },
  iconColor: { type: String, default: '#1565C0' },
  trend: { type: String, default: '' },
  trendPositive: { type: Boolean, default: true }
})

const iconBg = computed(() => {
  const hex = props.iconColor.replace('#', '')
  const r = parseInt(hex.slice(0, 2), 16)
  const g = parseInt(hex.slice(2, 4), 16)
  const b = parseInt(hex.slice(4, 6), 16)
  return `rgba(${r},${g},${b},0.15)`
})
</script>

<style scoped>
.stat-number { font-size: 2rem; font-weight: 700; line-height: 1; }
.stat-icon-bg {
  width: 56px; height: 56px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.stat-card:hover { transform: translateY(-2px); }
</style>
