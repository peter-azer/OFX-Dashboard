<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Analytics Overview</h1>
    <div class="counts mb-8">
      <div class="bg-blue-100 hover:bg-blue-200 border-blue-400 rounded shadow p-4 text-center">
        <div class="text-2xl text-blue-900 font-semibold">{{ data.brands ?? '-' }}</div>
        <div class="text-blue-500 mt-1">Brands</div>
      </div>
      <div class="bg-emerald-100 hover:bg-emerald-200 border-emerald-400  rounded shadow p-4 text-center">
        <div class="text-2xl text-emerald-900 font-semibold">{{ data.team ?? '-' }}</div>
        <div class="text-emerald-500 mt-1">Team Members</div>
      </div>
      <div class="bg-indigo-100 hover:bg-indigo-200 border-indigo-400 rounded shadow p-4 text-center">
        <div class="text-2xl text-indigo-900 font-semibold">{{ data.services ?? '-' }}</div>
        <div class="text-indigo-500 mt-1">Services</div>
      </div>
      <div class="bg-amber-100 hover:bg-amber-200 border-amber-400 rounded shadow p-4 text-center">
        <div class="text-2xl text-amber-900 font-semibold">{{ data.works ?? '-' }}</div>
        <div class="text-amber-500 mt-1">Works</div>
      </div>
    </div>
    <div class="bg-white shadow rounded p-6">
      <h2 class="font-semibold text-lg mb-4">Phone & WhatsApp Records (Last 7 Days)</h2>
      <canvas ref="chart" height="100"></canvas>
    </div>
  </div>
</template>

<script>
import api from '../../api';
import Chart from 'chart.js/auto';

export default {
  data() {
    return {
      data: { brands: 0, team: 0, services: 0, works: 0, chart: { labels: [], phone: [], whatsapp: [] } },
      chartInstance: null
    };
  },
  mounted() {
    api.get('/admin/analytics-overview').then(res => {
      this.data = res.data;
      this.renderChart();
    });
  },
  methods: {
    renderChart() {
      if (this.chartInstance) this.chartInstance.destroy();
      const ctx = this.$refs.chart;
      const { labels = [], phone = [], whatsapp = [] } = this.data.chart || {};
      this.chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [
            {
              label: 'Phone Records',
              data: phone,
              backgroundColor: 'rgba(37,99,235,0.5)'
            },
            {
              label: 'WhatsApp Records',
              data: whatsapp,
              backgroundColor: 'rgba(16,185,129,0.5)'
            }
          ]
        },
        options: {
          responsive: true,
          plugins: { legend: { position: 'top' }},
          scales: { x: { title: { display: true, text: 'Day' }}, y: { beginAtZero: true } }
        }
      });
    }
  }
};
</script>

<style scoped>

.counts {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}
</style>
