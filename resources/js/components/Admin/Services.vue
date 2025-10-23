<template>
  <div>
    <v-row class="mb-4" align="center">
      <v-col cols="6">
        <h2>Services</h2>
      </v-col>
      <v-col cols="6" class="text-right">
        <v-btn color="primary" @click="openCreate">New Service</v-btn>
      </v-col>
    </v-row>

    <v-data-table :items="items" :headers="headers" :loading="loading">
      <template #item.actions="{ item }">
        <v-btn size="small" variant="text" icon="mdi-pencil" @click="openEdit(item)" />
        <v-btn size="small" variant="text" color="error" icon="mdi-delete" @click="remove(item)" />
      </template>
    </v-data-table>

    <v-dialog v-model="dialog" max-width="700">
      <v-card>
        <v-card-title>{{ editing ? 'Edit Service' : 'Create Service' }}</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="save">
            <v-text-field v-model="form.service_name" label="Service Name" required />
            <v-textarea v-model="form.short_description" label="Short Description" required rows="3" />
            <v-text-field v-model="form.icon_url" label="Icon URL" required />
            <v-text-field v-model.number="form.order" type="number" label="Order" />
            <v-switch v-model="form.is_active" :true-value="true" :false-value="false" label="Active" />
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="dialog = false">Cancel</v-btn>
          <v-btn color="primary" :loading="saving" @click="save">Save</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="2500">{{ snackbar.text }}</v-snackbar>
  </div>
</template>

<script>
import api from '../../api';

export default {
  data() {
    return {
      loading: false,
      saving: false,
      items: [],
      dialog: false,
      editing: false,
      currentId: null,
      form: { service_name: '', short_description: '', icon_url: '', order: 0, is_active: true },
      headers: [
        { title: 'ID', key: 'id' },
        { title: 'Name', key: 'service_name' },
        { title: 'Order', key: 'order' },
        { title: 'Active', key: 'is_active' },
        { title: 'Actions', key: 'actions', sortable: false },
      ],
      snackbar: { show: false, text: '', color: 'success' },
    };
  },
  created() { this.fetch(); },
  methods: {
    notify(text, color = 'success') { this.snackbar = { show: true, text, color }; },
    fetch() {
      this.loading = true;
      api.get('/services').then(res => this.items = res.data).finally(() => this.loading = false);
    },
    openCreate() { this.editing = false; this.currentId = null; this.form = { service_name: '', short_description: '', icon_url: '', order: 0, is_active: true }; this.dialog = true; },
    openEdit(item) { this.editing = true; this.currentId = item.id; this.form = { service_name: item.service_name, short_description: item.short_description, icon_url: item.icon_url, order: item.order, is_active: item.is_active }; this.dialog = true; },
    save() {
      this.saving = true;
      const req = this.editing ? api.put(`/services/${this.currentId}`, this.form) : api.post('/services', this.form);
      req.then(() => { this.dialog = false; this.fetch(); this.notify('Saved successfully'); })
         .catch(() => this.notify('Save failed', 'error'))
         .finally(() => this.saving = false);
    },
    remove(item) {
      if (!confirm('Delete this service?')) return;
      api.delete(`/services/${item.id}`).then(() => { this.fetch(); this.notify('Deleted'); }).catch(() => this.notify('Delete failed', 'error'));
    },
  },
};
</script>
