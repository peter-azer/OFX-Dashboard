<template>
  <div>
    <v-row class="mb-4" align="center">
      <v-col cols="6">
        <h2>Email Contacts</h2>
      </v-col>
      <v-col cols="6" class="text-right">
        <v-btn color="primary" @click="openCreate">New Email</v-btn>
      </v-col>
    </v-row>

    <v-data-table :items="items" :headers="headers" :loading="loading">
      <template #item.services="{ item }">
        <div class="flex flex-wrap gap-1 max-w-xs">
          <v-chip v-for="service in item.services" :key="service.id" size="small" class="mr-1 mb-1">
            {{ service.service_name }}
          </v-chip>
        </div>
      </template>

      <template #item.is_active="{ item }">
        <v-switch :model-value="Boolean(item.is_active)" hide-details color="primary"
          @update:modelValue="toggleStatus(item, $event)" :disabled="loading" :loading="loading" class="mt-0 pt-0">
          <template v-slot:label>
            <span class="sr-only">Set as main email</span>
          </template>
        </v-switch>
      </template>
      <template #item.is_main="{ item }">
        <v-switch :model-value="Boolean(item.is_main)" hide-details color="primary" @update:modelValue="setAsMain(item)"
          :disabled="loading" :loading="loading" class="mt-0 pt-0">
          <template v-slot:label>
            <span class="sr-only">Set as main email</span>
          </template>
        </v-switch>
      </template>
      <template #item.actions="{ item }">
        <v-btn size="small" variant="text" icon @click="openEdit(item)">
          <v-icon size="18">
            <PencilSquareIcon />
          </v-icon>
        </v-btn>
        <v-btn size="small" variant="text" color="error" icon @click="openDelete(item)">
          <v-icon size="18">
            <TrashIcon />
          </v-icon>
        </v-btn>
      </template>
    </v-data-table>

    <v-dialog v-model="dialog" max-width="600">
      <v-card class="rounded-lg shadow-md">
        <v-card-title class="px-6 py-4 bg-gray-50 border-b border-gray-200">
          <span class="text-lg font-medium text-gray-900">{{ editing ? 'Edit Email' : 'Add New Email' }}</span>
        </v-card-title>
        <v-card-text class="px-6 py-4">
          <form @submit.prevent="save" class="space-y-4">
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
              <input id="email" v-model="form.email" type="email" required
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                placeholder="Enter email address">
              <p v-if="!form.email && formSubmitted" class="mt-1 text-sm text-red-600">Email is required</p>
              <p v-if="form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email) && formSubmitted"
                class="mt-1 text-sm text-red-600">
                Please enter a valid email address
              </p>
            </div>

            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Services</label>
              <v-select v-model="form.service_ids" :items="services" item-title="service_name" item-value="id"
                label="Select Services" multiple chips closable-chips clearable :loading="loading" :disabled="loading"
                class="w-full" variant="outlined" density="comfortable" hide-details="auto">
                <template #selection="{ item, index }">
                  <v-chip v-if="index < 2" size="small" class="mr-1" close @click:close="removeService(item.raw.id)">
                    {{ item.raw.service_name }}
                  </v-chip>
                  <span v-else-if="index === 2" class="text-gray-500 text-xs">
                    +{{ form.service_ids.length - 2 }} more
                  </span>
                </template>
                <template #no-data>
                  <div class="pa-2">
                    No services found. Please add services first.
                  </div>
                </template>
              </v-select>
            </div>
          </form>
        </v-card-text>
        <v-card-actions class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
          <div class="flex justify-end w-full space-x-3">
            <button type="button" @click="dialog = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
              Cancel
            </button>
            <button type="button" @click="save" :disabled="saving"
              class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
              <span v-if="saving" class="flex items-center text-blue-400">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg"
                  fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                Saving...
              </span>
              <span class="text-blue-500" v-else>Save</span>
            </button>
          </div>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="2500">
      {{ snackbar.text }}
    </v-snackbar>

    <v-dialog v-model="confirm.show" max-width="420">
      <v-card>
        <v-card-title>Confirm Delete</v-card-title>
        <v-card-text>Are you sure you want to delete this email?</v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="confirm.show = false">Cancel</v-btn>
          <v-btn color="error" :loading="confirm.loading" @click="confirmDelete">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import api from '../../api';
import { PencilSquareIcon, TrashIcon, EnvelopeIcon } from '@heroicons/vue/24/outline';

export default {
  components: { PencilSquareIcon, TrashIcon, EnvelopeIcon },
  data() {
    return {
      loading: false,
      saving: false,
      items: [],
      services: [],
      mainEmail: null,
      dialog: false,
      editing: false,
      currentId: null,
      form: {
        email: '',
        is_active: true,
        is_main: false,
        service_ids: [],
      },
      formSubmitted: false,
      confirm: { show: false, item: null, loading: false },
      headers: [
        { title: 'Email', key: 'email' },
        { title: 'Services', key: 'services', sortable: false },
        { title: 'Active', key: 'is_active', sortable: false },
        { title: 'Main', key: 'is_main', sortable: false },
        { title: 'Actions', key: 'actions', sortable: false },
      ],
      snackbar: { show: false, text: '', color: 'success' },
    };
  },
  async created() {
    await this.fetch();
  },
  methods: {
    notify(text, color = 'success') {
      this.snackbar = { show: true, text, color };
    },

    async fetch() {
      this.loading = true;
      try {
        const response = await api.get('/emails', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        this.items = response.data.data || [];
        this.services = response.data.services || [];

        // Find and set the main email
        const mainItem = this.items.find(item => item.is_main);
        this.mainEmail = mainItem ? mainItem.id : null;

        // Ensure consistent state
        this.items = this.items.map(item => ({
          ...item,
          is_main: item.id === this.mainEmail ? 1 : 0
        }));

        console.log('Fetched emails and services:', { items: this.items, services: this.services });
      } catch (error) {
        console.error('Error fetching data:', error);
        if (error.response && error.response.status === 401) {
          this.notify('Session expired. Please log in again.', 'error');
          this.$router.push('/login');
        } else {
          this.notify('Failed to load emails', 'error');
        }
      } finally {
        this.loading = false;
      }
    },

    openCreate() {
      this.editing = false;
      this.currentId = null;
      this.formSubmitted = false;
      this.form = {
        email: '',
        is_active: true,
        is_main: false,
      };
      this.dialog = true;
    },

    openEdit(item) {
      this.editing = true;
      this.currentId = item.id;
      this.form = {
        email: item.email,
        is_active: item.is_active,
        is_main: item.is_main,
        service_ids: item.services ? item.services.map(s => s.id) : []
      };
      this.dialog = true;
    },

    async save() {
      this.formSubmitted = true;

      // Basic validation
      if (!this.form.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
        return;
      }

      this.saving = true;

      try {
        const data = {
          email: this.form.email,
          service_ids: this.form.service_ids || []
        };

        if (this.editing) {
          await api.put(`/emails/${this.currentId}`, data);
          this.notify('Email updated successfully');
        } else {
          await api.post('/emails', data);
          this.notify('Email added successfully');
        }

        this.dialog = false;
        await this.fetch();
      } catch (error) {
        console.error('Error saving email:', error);
        this.notify(error.response?.data?.message || 'Failed to save email', 'error');
      } finally {
        this.saving = false;
      }
    },

    openDelete(item) {
      this.confirm = { show: true, item, loading: false };
    },

    confirmDelete() {
      if (!this.confirm.item) return;
      this.confirm.loading = true;
      api.delete(`/emails/${this.confirm.item.id}`)
        .then(() => {
          this.fetch();
          this.notify('Email deleted');
          this.confirm.show = false;
        })
        .catch(() => this.notify('Failed to delete email', 'error'))
        .finally(() => {
          this.confirm.loading = false;
          this.confirm.item = null;
        });
    },

    toggleStatus(item, newValue) {
      // Ensure we're working with a boolean value
      const newStatus = Boolean(newValue);

      // Save the current state in case we need to revert
      const previousState = Boolean(item.is_active);

      // Update the local state immediately for better UX
      item.is_active = newStatus ? 1 : 0;

      // Force re-render
      this.$forceUpdate();

      // Make the API call to update the status
      api.put(`/emails/${item.id}/toggle-status`, {
        is_active: newStatus
      }, {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      })
        .then((response) => {
          // Update with the server's response
          if (response.data.success) {
            const updatedItem = response.data.data;
            const itemIndex = this.items.findIndex(i => i.id === updatedItem.id);
            if (itemIndex !== -1) {
              this.items[itemIndex].is_active = updatedItem.is_active;
            }
          }
        })
        .catch((error) => {
          console.error('Error toggling status:', error);
          // Revert the UI on error
          item.is_active = previousState ? 1 : 0;
          this.notify('Failed to update email status', 'error');
          // Force re-render
          this.$forceUpdate();
        });
    },

    async setAsMain(item) {
      // Don't do anything if clicking the already selected main email
      if (this.mainEmail === item.id) return;

      const originalMainEmail = this.mainEmail;
      this.loading = true;

      try {
        // Update UI immediately for better UX
        this.mainEmail = item.id;
        this.items = this.items.map(email => ({
          ...email,
          is_main: email.id === item.id ? 1 : 0
        }));

        // Make the API call
        const response = await api.put(`/emails/${item.id}/set-main`);

        if (!response.data.success) {
          throw new Error('Failed to update main email');
        }

        this.notify('Main email updated successfully', 'success');
      } catch (error) {
        console.error('Error setting main email:', error);
        // Revert on error
        this.mainEmail = originalMainEmail;
        this.items = this.items.map(email => ({
          ...email,
          is_main: email.id === originalMainEmail ? 1 : 0
        }));
        this.notify(error.response?.data?.message || 'Failed to set as main email', 'error');
      } finally {
        this.loading = false;
      }
    },

    removeService(serviceId) {
      this.form.service_ids = this.form.service_ids.filter(id => id !== serviceId);
    }
  },
};
</script>

<style scoped>
/* Add any custom styles here */
</style>
