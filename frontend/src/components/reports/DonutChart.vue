<template>
  <v-card elevation="2">
    <v-card-title class="text-body-1 font-weight-bold pa-4 pb-0">{{ title }}</v-card-title>
    <v-card-text class="pa-4">
      <div style="position: relative; height: 200px; max-width: 200px; margin: 0 auto;">
        <canvas ref="chartRef"></canvas>
      </div>
      <div class="chart-legend mt-3">
        <div v-for="(item, i) in items" :key="i" class="legend-item">
          <span class="legend-dot" :style="{ background: chartColors[i] }"></span>
          <span class="text-body-2">{{ item.label }}</span>
          <span class="text-body-2 font-weight-medium ml-auto">{{ item.value }}</span>
        </div>
      </div>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue'
import {
  Chart,
  DoughnutController,
  ArcElement,
  Tooltip,
  Legend
} from 'chart.js'

Chart.register(DoughnutController, ArcElement, Tooltip, Legend)

const props = defineProps({
  title: { type: String, default: '' },
  items: { type: Array, default: () => [] },
  colors: { type: Array, default: () => ['#1565C0', '#00897B', '#FF6F00', '#6A1B9A', '#B00020'] }
})

const chartRef = ref(null)
const chartColors = props.colors
let chartInstance = null

function buildChart() {
  if (chartInstance) chartInstance.destroy()
  if (!chartRef.value || !props.items.length) return

  chartInstance = new Chart(chartRef.value, {
    type: 'doughnut',
    data: {
      labels: props.items.map(i => i.label),
      datasets: [{
        data: props.items.map(i => i.value || 0),
        backgroundColor: chartColors,
        borderWidth: 2,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%',
      plugins: {
        legend: { display: false },
        tooltip: { enabled: true }
      }
    }
  })
}

onMounted(buildChart)
watch(() => props.items, buildChart, { deep: true })
onUnmounted(() => { if (chartInstance) chartInstance.destroy() })
</script>

<style scoped>
.chart-legend { display: flex; flex-direction: column; gap: 6px; }
.legend-item { display: flex; align-items: center; gap: 8px; }
.legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
</style>
