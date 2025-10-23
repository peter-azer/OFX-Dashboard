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
        <v-btn size="small" variant="text" icon="fas fa-pen" @click="openEdit(item)" />
        <v-btn size="small" variant="text" color="error" icon="fas fa-trash" @click="openDelete(item)" />
      </template>
    </v-data-table>

    <v-dialog v-model="dialog" max-width="800">
      <v-card>
        <v-card-title>{{ editing ? 'Edit Member' : 'Create Member' }}</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="save">
            <v-text-field v-model="form.member_name" label="Name" required />
            <v-text-field v-model="form.position" label="Position" required />
            <v-textarea v-model="form.bio" label="Bio" rows="3" required />
            <v-file-input
              v-model="form.photo_url"
              label="Upload Photo"
              accept="image/*"
              prepend-icon="fas fa-image"
              show-size
              clearable
            />
            <v-text-field v-model="form.facebook_link" label="Facebook Link" />
            <v-text-field v-model="form.linkedin_link" label="LinkedIn Link" />
            <v-text-field v-model="form.twitter_link" label="Twitter Link" />
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
      form: {
        member_name: '', position: '', bio: '', photo_url: null,
        facebook_link: '', linkedin_link: '', twitter_link: '', is_active: true,
      },
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
    openCreate() { this.editing = false; this.currentId = null; this.form = { member_name: '', position: '', bio: '', photo_url: null, facebook_link: '', linkedin_link: '', twitter_link: '', is_active: true }; this.dialog = true; },
    openEdit(item) { this.editing = true; this.currentId = item.id; this.form = { member_name: item.member_name, position: item.position, bio: item.bio, photo_url: null, facebook_link: item.facebook_link, linkedin_link: item.linkedin_link, twitter_link: item.twitter_link, is_active: item.is_active }; this.dialog = true; },
    save() {
      this.saving = true;
      const fd = new FormData();
      fd.append('member_name', this.form.member_name);
      fd.append('position', this.form.position);
      fd.append('bio', this.form.bio);
      const imgFile = Array.isArray(this.form.photo_url) ? this.form.photo_url[0] : this.form.photo_url;
      if (imgFile instanceof File) fd.append('photo_url', imgFile);
      if (this.form.facebook_link) fd.append('facebook_link', this.form.facebook_link);
      if (this.form.linkedin_link) fd.append('linkedin_link', this.form.linkedin_link);
      if (this.form.twitter_link) fd.append('twitter_link', this.form.twitter_link);
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
