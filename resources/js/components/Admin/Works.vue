<template>
  <div>
    <v-row class="mb-4" align="center">
      <v-col cols="6">
        <h2>Works</h2>
      </v-col>
      <v-col cols="6" class="text-right">
        <v-btn color="primary" @click="openCreate">New Work</v-btn>
      </v-col>
    </v-row>

    <v-data-table :items="items" :headers="headers" :loading="loading">
      <template #item.project_image="{ item }">
        <v-img :src="item.project_image" width="60" height="40" cover></v-img>
      </template>
      <template #item.service="{ item }">
        {{ item.service?.service_name || '-' }}
      </template>
      <template #item.actions="{ item }">
        <v-btn size="small" variant="text" icon="fas fa-pen" @click="openEdit(item)" />
        <v-btn size="small" variant="text" color="error" icon="fas fa-trash" @click="openDelete(item)" />
      </template>
    </v-data-table>

    <v-dialog v-model="dialog" max-width="800">
      <v-card>
        <v-card-title>{{ editing ? 'Edit Work' : 'Create Work' }}</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="save">
            <v-text-field v-model="form.project_title" label="Project Title" required />
            <v-text-field v-model="form.project_title_ar" label="Project Title (AR)" required />
            <v-textarea v-model="form.project_description" label="Project Description" required rows="4" />
            <v-textarea v-model="form.project_description_ar" label="Project Description (AR)" required rows="4" />
            <v-file-input
              v-model="form.project_image"
              label="Upload Project Image"
              accept="image/*"
              prepend-icon="fas fa-image"
              show-size
              clearable
              :required="!editing"
            />
            <v-text-field v-model="form.project_link" label="Project Link" />
            <v-select
              v-model="form.service_id"
              :items="services"
              item-title="service_name"
              item-value="id"
              label="Service"
              required
            />
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

    <v-dialog v-model="confirm.show" max-width="420">
      <v-card>
        <v-card-title>Confirm Delete</v-card-title>
        <v-card-text>Are you sure you want to delete this work?</v-card-text>
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

export default {
  data() {
    return {
      loading: false,
      saving: false,
      items: [],
      services: [],
      dialog: false,
      editing: false,
      currentId: null,
      form: { project_title: '', project_title_ar: '', project_description: '', project_description_ar: '', project_image: null, project_link: '', service_id: null, is_active: true },
      confirm: { show: false, item: null, loading: false },
      headers: [
        { title: 'ID', key: 'id' },
        { title: 'Title', key: 'project_title' },
        { title: 'Image', key: 'project_image' },
        { title: 'Service', key: 'service' },
        { title: 'Active', key: 'is_active' },
        { title: 'Actions', key: 'actions', sortable: false },
      ],
      snackbar: { show: false, text: '', color: 'success' },
    };
  },
  created() { this.fetch(); this.fetchServices(); },
  methods: {
    notify(text, color = 'success') { this.snackbar = { show: true, text, color }; },
    fetch() { this.loading = true; api.get('/works').then(res => this.items = res.data).finally(() => this.loading = false); },
    fetchServices() { api.get('/services').then(res => this.services = res.data); },
    openCreate() { this.editing = false; this.currentId = null; this.form = { project_title: '', project_title_ar: '', project_description: '', project_description_ar: '', project_image: null, project_link: '', service_id: null, is_active: true }; this.dialog = true; },
    openEdit(item) { this.editing = true; this.currentId = item.id; this.form = { project_title: item.project_title, project_title_ar: item.project_title_ar, project_description: item.project_description, project_description_ar: item.project_description_ar, project_image: null, project_link: item.project_link, service_id: item.service_id || item.service?.id || null, is_active: item.is_active }; this.dialog = true; },
    save() {
      this.saving = true;
      const fd = new FormData();
      fd.append('project_title', this.form.project_title);
      fd.append('project_title_ar', this.form.project_title_ar);
      fd.append('project_description', this.form.project_description);
      fd.append('project_description_ar', this.form.project_description_ar);
      const imgFile = Array.isArray(this.form.project_image) ? this.form.project_image[0] : this.form.project_image;
      if (imgFile instanceof File) fd.append('project_image', imgFile);
      if (this.form.project_link) fd.append('project_link', this.form.project_link);
      if (this.form.service_id) fd.append('service_id', String(this.form.service_id));
      fd.append('is_active', this.form.is_active ? '1' : '0');

      const req = this.editing
        ? (fd.append('_method', 'PUT'), api.post(`/works/${this.currentId}`, fd))
        : api.post('/works', fd);
      req.then(() => { this.dialog = false; this.fetch(); this.notify('Saved successfully'); })
         .catch(() => this.notify('Save failed', 'error'))
         .finally(() => this.saving = false);
    },
    openDelete(item) { this.confirm = { show: true, item, loading: false }; },
    confirmDelete() {
      if (!this.confirm.item) return;
      this.confirm.loading = true;
      api.delete(`/works/${this.confirm.item.id}`)
        .then(() => { this.fetch(); this.notify('Deleted'); this.confirm.show = false; })
        .catch(() => this.notify('Delete failed', 'error'))
        .finally(() => { this.confirm.loading = false; this.confirm.item = null; });
    },
  },
};
</script>
