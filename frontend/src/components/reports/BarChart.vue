<template>
  <v-card elevation="2">
    <v-card-title class="text-body-1 font-weight-bold pa-4 pb-0">{{ title }}</v-card-title>
    <v-card-text class="pa-4">
      <div style="position: relative; height: 250px;">
        <canvas ref="chartRef"></canvas>
      </div>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue'
import {
  Chart,
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend
} from 'chart.js'

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend)

const props = defineProps({
  title: { type: String, default: '' },
  labels: { type: Array, default: () => [] },
  data: { type: Array, default: () => [] },
  color: { type: String, default: '#1565C0' }
})

const chartRef = ref(null)
let chartInstance = null

function buildChart() {
  if (chartInstance) chartInstance.destroy()
  if (!chartRef.value) return

  chartInstance = new Chart(chartRef.value, {
    type: 'bar',
    data: {
      labels: props.labels,
      datasets: [{
        label: props.title,
        data: props.data,
        backgroundColor: props.color + 'CC',
        borderColor: props.color,
        borderWidth: 1,
        borderRadius: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { enabled: true }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { stepSize: 1 },
          grid: { color: 'rgba(0,0,0,0.05)' }
        },
        x: {
          grid: { display: false }
        }
      }
    }
  })
}

onMounted(buildChart)
watch([() => props.labels, () => props.data], buildChart, { deep: true })
onUnmounted(() => { if (chartInstance) chartInstance.destroy() })
</script>
