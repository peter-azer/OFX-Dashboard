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
      <template #item.icon_url="{ item }">
        <v-img :src="item.icon_url" width="40" height="40" cover></v-img>
      </template>
      <template #item.actions="{ item }">
        <v-btn size="small" variant="text" icon="fas fa-pen" @click="openEdit(item)" />
        <v-btn size="small" variant="text" color="error" icon="fas fa-trash" @click="openDelete(item)" />
      </template>
    </v-data-table>

    <v-dialog v-model="dialog" max-width="700">
      <v-card>
        <v-card-title>{{ editing ? 'Edit Service' : 'Create Service' }}</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="save">
            <v-text-field v-model="form.service_name" label="Service Name" required />
            <v-textarea v-model="form.short_description" label="Short Description" required rows="3" />
            <v-file-input
              v-model="form.icon_url"
              label="Upload Icon"
              accept="image/*,image/svg+xml"
              prepend-icon="fas fa-image"
              show-size
              clearable
            />
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
      form: { service_name: '', short_description: '', icon_url: null, order: 0, is_active: true },
      headers: [
        { title: 'ID', key: 'id' },
        { title: 'Name', key: 'service_name' },
        { title: 'Icon', key: 'icon_url' },
        { title: 'Order', key: 'order' },
        { title: 'Active', key: 'is_active' },
        { title: 'Actions', key: 'actions', sortable: false },
      ],
      snackbar: { show: false, text: '', color: 'success' },
      confirm: { show: false, item: null, loading: false },
    };
  },
  created() { this.fetch(); },
  methods: {
    notify(text, color = 'success') { this.snackbar = { show: true, text, color }; },
    fetch() {
      this.loading = true;
      api.get('/services').then(res => this.items = res.data).finally(() => this.loading = false);
    },
    openCreate() { this.editing = false; this.currentId = null; this.form = { service_name: '', short_description: '', icon_url: null, order: 0, is_active: true }; this.dialog = true; },
    openEdit(item) { this.editing = true; this.currentId = item.id; this.form = { service_name: item.service_name, short_description: item.short_description, icon_url: null, order: item.order, is_active: item.is_active }; this.dialog = true; },
    save() {
      this.saving = true;
      const fd = new FormData();
      fd.append('service_name', this.form.service_name);
      fd.append('short_description', this.form.short_description);
      const iconFile = Array.isArray(this.form.icon_url) ? this.form.icon_url[0] : this.form.icon_url;
      if (iconFile instanceof File) fd.append('icon_url', iconFile);
      if (this.form.order !== null && this.form.order !== undefined) fd.append('order', this.form.order);
      fd.append('is_active', this.form.is_active ? '1' : '0');

      const req = this.editing
        ? (fd.append('_method', 'PUT'), api.post(`/services/${this.currentId}`, fd))
        : api.post('/services', fd);
      req.then(() => { this.dialog = false; this.fetch(); this.notify('Saved successfully'); })
         .catch(() => this.notify('Save failed', 'error'))
         .finally(() => this.saving = false);
    },
    openDelete(item) { this.confirm = { show: true, item, loading: false }; },
    confirmDelete() {
      if (!this.confirm.item) return;
      this.confirm.loading = true;
      api.delete(`/services/${this.confirm.item.id}`)
        .then(() => { this.fetch(); this.notify('Deleted'); this.confirm.show = false; })
        .catch(() => this.notify('Delete failed', 'error'))
        .finally(() => { this.confirm.loading = false; this.confirm.item = null; });
    },
  },
};
</script>
