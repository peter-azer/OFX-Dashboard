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
      <template #item.actions="{ item }">
        <v-btn size="small" variant="text" icon="mdi-pencil" @click="openEdit(item)" />
        <v-btn size="small" variant="text" color="error" icon="mdi-delete" @click="remove(item)" />
      </template>
    </v-data-table>

    <v-dialog v-model="dialog" max-width="800">
      <v-card>
        <v-card-title>{{ editing ? 'Edit Work' : 'Create Work' }}</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="save">
            <v-text-field v-model="form.project_title" label="Project Title" required />
            <v-textarea v-model="form.project_description" label="Project Description" required rows="4" />
            <v-text-field v-model="form.project_image" label="Project Image URL" required />
            <v-text-field v-model="form.project_link" label="Project Link" />
            <v-text-field v-model="form.category" label="Category" required />
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
      form: { project_title: '', project_description: '', project_image: '', project_link: '', category: '', is_active: true },
      headers: [
        { title: 'ID', key: 'id' },
        { title: 'Title', key: 'project_title' },
        { title: 'Category', key: 'category' },
        { title: 'Active', key: 'is_active' },
        { title: 'Actions', key: 'actions', sortable: false },
      ],
      snackbar: { show: false, text: '', color: 'success' },
    };
  },
  created() { this.fetch(); },
  methods: {
    notify(text, color = 'success') { this.snackbar = { show: true, text, color }; },
    fetch() { this.loading = true; api.get('/works').then(res => this.items = res.data).finally(() => this.loading = false); },
    openCreate() { this.editing = false; this.currentId = null; this.form = { project_title: '', project_description: '', project_image: '', project_link: '', category: '', is_active: true }; this.dialog = true; },
    openEdit(item) { this.editing = true; this.currentId = item.id; this.form = { project_title: item.project_title, project_description: item.project_description, project_image: item.project_image, project_link: item.project_link, category: item.category, is_active: item.is_active }; this.dialog = true; },
    save() {
      this.saving = true;
      const req = this.editing ? api.put(`/works/${this.currentId}`, this.form) : api.post('/works', this.form);
      req.then(() => { this.dialog = false; this.fetch(); this.notify('Saved successfully'); })
         .catch(() => this.notify('Save failed', 'error'))
         .finally(() => this.saving = false);
    },
    remove(item) { if (!confirm('Delete this work?')) return; api.delete(`/works/${item.id}`).then(() => { this.fetch(); this.notify('Deleted'); }).catch(() => this.notify('Delete failed', 'error')); },
  },
};
</script>
