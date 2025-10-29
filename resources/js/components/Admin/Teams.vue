<template>
  <div>
    <v-row class="mb-4" align="center">
      <v-col cols="6">
        <h2>Teams</h2>
      </v-col>
      <v-col cols="6" class="text-right">
        <v-btn color="primary" @click="openCreate">New Member</v-btn>
      </v-col>
    </v-row>

    <v-data-table :items="items" :headers="headers" :loading="loading">
      <template #item.photo_url="{ item }">
        <v-img :src="item.photo_url" width="60" height="40" cover></v-img>
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

    <v-dialog v-model="dialog" max-width="800">
      <v-card>
        <v-card-title>{{ editing ? 'Edit Member' : 'Create Member' }}</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="save">
            <v-text-field v-model="form.member_name" label="Name" required />
            <v-text-field v-model="form.member_name_ar" label="Name (AR)" required />
            <v-text-field v-model="form.position" label="Position" required />
            <v-text-field v-model="form.position_ar" label="Position (AR)" required />
            <v-textarea v-model="form.bio" label="Bio" rows="3" required />
            <v-textarea v-model="form.bio_ar" label="Bio (AR)" rows="3" required />
            <v-file-input
              v-model="form.photo_url"
              label="Upload Photo"
              accept="image/*"
              show-size
              clearable
              :required="!editing"
            >
              <template #prepend>
                <PhotoIcon class="h-5 w-5" />
              </template>
            </v-file-input>
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
import { PencilSquareIcon, TrashIcon, PhotoIcon } from '@heroicons/vue/24/outline';

export default {
  components: { PencilSquareIcon, TrashIcon, PhotoIcon },
  data() {
    return {
      loading: false,
      saving: false,
      items: [],
      dialog: false,
      editing: false,
      currentId: null,
      form: { member_name: '', member_name_ar: '', position: '', position_ar: '', bio: '', bio_ar: '', photo_url: null, is_active: true },
      headers: [
        { title: 'ID', key: 'id' },
        { title: 'Name', key: 'member_name' },
        { title: 'Position', key: 'position' },
        { title: 'Photo', key: 'photo_url' },
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
    fetch() { this.loading = true; api.get('/teams').then(res => this.items = res.data).finally(() => this.loading = false); },
    openCreate() { this.editing = false; this.currentId = null; this.form = { member_name: '', member_name_ar: '', position: '', position_ar: '', bio: '', bio_ar: '', photo_url: null, is_active: true }; this.dialog = true; },
    openEdit(item) { this.editing = true; this.currentId = item.id; this.form = { member_name: item.member_name, member_name_ar: item.member_name_ar, position: item.position, position_ar: item.position_ar, bio: item.bio, bio_ar: item.bio_ar, photo_url: null, is_active: item.is_active }; this.dialog = true; },
    save() {
      this.saving = true;
      const fd = new FormData();
      fd.append('member_name', this.form.member_name);
      fd.append('member_name_ar', this.form.member_name_ar);
      fd.append('position', this.form.position);
      fd.append('position_ar', this.form.position_ar);
      fd.append('bio', this.form.bio);
      fd.append('bio_ar', this.form.bio_ar);
      const imgFile = Array.isArray(this.form.photo_url) ? this.form.photo_url[0] : this.form.photo_url;
      if (imgFile instanceof File) fd.append('photo_url', imgFile);
      fd.append('is_active', this.form.is_active ? '1' : '0');

      const req = this.editing
        ? (fd.append('_method', 'PUT'), api.post(`/teams/${this.currentId}`, fd))
        : api.post('/teams', fd);
      req.then(() => { this.dialog = false; this.fetch(); this.notify('Saved successfully'); })
         .catch(() => this.notify('Save failed', 'error'))
         .finally(() => this.saving = false);
    },
    openDelete(item) { this.confirm = { show: true, item, loading: false }; },
    confirmDelete() {
      if (!this.confirm.item) return;
      this.confirm.loading = true;
      api.delete(`/teams/${this.confirm.item.id}`)
        .then(() => { this.fetch(); this.notify('Deleted'); this.confirm.show = false; })
        .catch(() => this.notify('Delete failed', 'error'))
        .finally(() => { this.confirm.loading = false; this.confirm.item = null; });
    },
  },
};
</script>
