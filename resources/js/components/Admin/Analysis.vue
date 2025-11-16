<template>
  <div class="p-6 bg-gray-50 rounded-lg shadow-sm">
    <!-- Filters -->
    <div class="mb-8">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">Filters</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="space-y-1">
          <label class="block text-sm font-medium text-gray-700">Time Range</label>
          <select v-model="filters.days" @change="fetchData" 
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            <option :value="7">Last 7 days</option>
            <option :value="30">Last 30 days</option>
            <option :value="90">Last 90 days</option>
            <option :value="365">Last year</option>
          </select>
        </div>
        
        <div class="space-y-1">
          <label class="block text-sm font-medium text-gray-700">Group By</label>
          <select v-model="filters.period" @change="fetchData"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            <option value="day">Daily</option>
            <option value="week">Weekly</option>
            <option value="month">Monthly</option>
          </select>
        </div>
        
        <div class="space-y-1 lg:col-span-2">
          <label class="block text-sm font-medium text-gray-700">Search by Name</label>
          <div class="flex rounded-md shadow-sm">
            <input type="text" 
                   v-model="filters.name" 
                   placeholder="Enter name to filter..." 
                   class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-l-md border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                   @keyup.enter="fetchData">
            <button @click="fetchData" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-r-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              Search
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Total Brands</h3>
        <p class="text-3xl font-bold">{{ stats.brands }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Team Members</h3>
        <p class="text-3xl font-bold">{{ stats.team }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Services</h3>
        <p class="text-3xl font-bold">{{ stats.services }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Works</h3>
        <p class="text-3xl font-bold">{{ stats.works }}</p>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Contacts Overview</h3>
        <BarChart v-if="chartData.labels.length" :data="chartData" :options="chartOptions" />
        <div v-else class="text-gray-500 text-center py-8">
          No contact data available for the selected filters
        </div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Activity Over Time</h3>
        <LineChart v-if="chartData.labels.length" :data="chartData" :options="chartOptions" />
        <div v-else class="text-gray-500 text-center py-8">
          No activity data available for the selected period
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import BarChart from '@/Components/Charts/BarChart.vue';
import LineChart from '@/Components/Charts/LineChart.vue';
import api from '../../api';
import Chart from 'chart.js/auto';

export default {
  components: {
    Head,
    Link,
    BarChart,
    LineChart,
  },
  setup() {
    const stats = ref({
      brands: 0,
      team: 0,
      services: 0,
      works: 0,
    });

    const chartData = ref({
      labels: [],
      datasets: [
        {
          label: 'Phone',
          backgroundColor: '#4F46E5',
          data: [],
        },
        {
          label: 'WhatsApp',
          backgroundColor: '#10B981',
          data: [],
        },
      ],
    });

    const periodData = ref({
      labels: [],
      phone: [],
      whatsapp: []
    });


    const filters = ref({
      days: 30,
      name: '',
      period: 'day'
    });

    const chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: { grid: { display: false } },
        y: { 
          beginAtZero: true,
          ticks: { stepSize: 1 }
        }
      }
    };

    const periodChartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: { grid: { display: false } },
        y: { 
          beginAtZero: true,
          ticks: { stepSize: 1 }
        }
      }
    };

    const periodChartData = computed(() => ({
      labels: periodData.value.labels,
      datasets: [
        {
          label: 'Phone',
          data: periodData.value.phone,
          borderColor: '#4F46E5',
          backgroundColor: 'rgba(79, 70, 229, 0.1)',
          tension: 0.3,
          fill: true
        },
        {
          label: 'WhatsApp',
          data: periodData.value.whatsapp,
          borderColor: '#10B981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          tension: 0.3,
          fill: true
        }
      ]
    }));

    const fetchData = async () => {
      try {
        const params = new URLSearchParams();
        Object.entries(filters.value).forEach(([key, value]) => {
          if (value) params.append(key, value);
        });

        const response = await api.get(`/analytics-overview?${params.toString()}`);
        
        // Update stats
        stats.value = {
          brands: response.data.brands,
          team: response.data.team,
          services: response.data.services,
          works: response.data.works
        };

        // Update main chart
        chartData.value = {
          ...chartData.value,
          labels: response.data.chart.labels,
          datasets: [
            {
              ...chartData.value.datasets[0],
              data: response.data.chart.phone
            },
            {
              ...chartData.value.datasets[1],
              data: response.data.chart.whatsapp
            }
          ]
        };

        // Update period data
        if (response.data.periodData) {
          periodData.value = response.data.periodData;
        }
      } catch (error) {
        console.error('Error fetching analytics data:', error);
        // You might want to show an error message to the user here
      }
    };

    // Call fetchData on component mount
    onMounted(() => {
      fetchData();
    });

    return {
      stats,
      filters,
      chartData,
      chartOptions,
      periodChartData,
      periodChartOptions,
      fetchData
    };
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
