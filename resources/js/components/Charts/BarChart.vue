<template>
  <div class="chart-container">
    <canvas ref="chart"></canvas>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import Chart from 'chart.js/auto';

export default {
  name: 'BarChart',
  props: {
    data: {
      type: Object,
      required: true
    },
    options: {
      type: Object,
      default: () => ({})
    }
  },
  setup(props) {
    const chart = ref(null);
    let chartInstance = null;

    const renderChart = () => {
      if (chartInstance) {
        chartInstance.destroy();
      }

      if (chart.value) {
        const ctx = chart.value.getContext('2d');
        chartInstance = new Chart(ctx, {
          type: 'bar',
          data: props.data,
          options: {
            responsive: true,
            maintainAspectRatio: false,
            ...props.options
          }
        });
      }
    };

    onMounted(renderChart);

    watch(
      () => props.data,
      () => {
        renderChart();
      },
      { deep: true }
    );

    return {
      chart
    };
  }
};
</script>

<style scoped>
.chart-container {
  position: relative;
  height: 300px;
}
</style>
