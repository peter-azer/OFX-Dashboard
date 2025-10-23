<template>
  <div>
    <v-row class="mb-4" align="center">
      <v-col cols="6">
        <h2>Abouts</h2>
      </v-col>
      <v-col cols="6" class="text-right">
        <v-btn color="primary" @click="openCreate">New About</v-btn>
      </v-col>
    </v-row>

    <v-data-table :items="items" :headers="headers" :loading="loading">
      <template #item.image_url="{ item }">
        <v-img :src="item.image_url" width="60" height="40" cover></v-img>
      </template>
      <template #item.actions="{ item }">
        <v-btn size="small" variant="text" icon="fas fa-pen" @click="openEdit(item)" />
        <v-btn size="small" variant="text" color="error" icon="fas fa-trash" @click="openDelete(item)" />
      </template>
    </v-data-table>

    <v-dialog v-model="dialog" max-width="700">
      <v-card>
        <v-card-title>{{ editing ? 'Edit About' : 'Create About' }}</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="save">
            <v-text-field v-model="form.title" label="Title" required />
            <v-textarea v-model="form.description" label="Description" required rows="4" />
            <v-file-input
              v-model="form.image_url"
              label="Upload Image"
              accept="image/*"
              prepend-icon="fas fa-image"
              show-size
              clearable
            />
            <v-text-field v-model="form.video_url" label="Video URL" />
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
        <v-card-text>Are you sure you want to delete this about?</v-card-text>
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
      dialog: false,
      editing: false,
      currentId: null,
      form: { title: '', description: '', image_url: null, video_url: '', is_active: true },
      confirm: { show: false, item: null, loading: false },
      headers: [
        { title: 'ID', key: 'id' },
        { title: 'Title', key: 'title' },
        { title: 'Image', key: 'image_url' },
        { title: 'Active', key: 'is_active' },
        { title: 'Actions', key: 'actions', sortable: false },
      ],
      snackbar: { show: false, text: '', color: 'success' },
    };
  },
  created() {
    this.fetch();
  },
  methods: {
    notify(text, color = 'success') { this.snackbar = { show: true, text, color }; },
    fetch() {
      this.loading = true;
      api.get('/abouts').then(res => this.items = res.data).finally(() => this.loading = false);
    },
    openCreate() {
      this.editing = false; this.currentId = null;
      this.form = { title: '', description: '', image_url: null, video_url: '', is_active: true };
      this.dialog = true;
    },
    openEdit(item) {
      this.editing = true; this.currentId = item.id;
      this.form = { title: item.title, description: item.description, image_url: null, video_url: item.video_url, is_active: item.is_active };
      this.dialog = true;
    },
    save() {
      this.saving = true;
      const fd = new FormData();
      fd.append('title', this.form.title);
      fd.append('description', this.form.description);
      const imgFile = Array.isArray(this.form.image_url) ? this.form.image_url[0] : this.form.image_url;
      if (imgFile instanceof File) {
        fd.append('image_url', imgFile);
      }
      if (this.form.video_url) fd.append('video_url', this.form.video_url);
      fd.append('is_active', this.form.is_active ? '1' : '0');

      const req = this.editing
        ? (fd.append('_method', 'PUT'), api.post(`/abouts/${this.currentId}`, fd))
        : api.post('/abouts', fd);
      req.then(() => { this.dialog = false; this.fetch(); this.notify('Saved successfully'); })
         .catch(() => this.notify('Save failed', 'error'))
         .finally(() => this.saving = false);
    },
    openDelete(item) { this.confirm = { show: true, item, loading: false }; },
    confirmDelete() {
      if (!this.confirm.item) return;
      this.confirm.loading = true;
      api.delete(`/abouts/${this.confirm.item.id}`)
        .then(() => { this.fetch(); this.notify('Deleted'); this.confirm.show = false; })
        .catch(() => this.notify('Delete failed', 'error'))
        .finally(() => { this.confirm.loading = false; this.confirm.item = null; });
    },
  },
};
</script>
