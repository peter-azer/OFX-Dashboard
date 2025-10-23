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
      <template #item.actions="{ item }">
        <v-btn size="small" variant="text" icon="mdi-pencil" @click="openEdit(item)" />
        <v-btn size="small" variant="text" color="error" icon="mdi-delete" @click="remove(item)" />
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
            <v-text-field v-model="form.photo_url" label="Photo URL" required />
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
        member_name: '', position: '', bio: '', photo_url: '',
        facebook_link: '', linkedin_link: '', twitter_link: '', is_active: true,
      },
      headers: [
        { title: 'ID', key: 'id' },
        { title: 'Name', key: 'member_name' },
        { title: 'Position', key: 'position' },
        { title: 'Active', key: 'is_active' },
        { title: 'Actions', key: 'actions', sortable: false },
      ],
      snackbar: { show: false, text: '', color: 'success' },
    };
  },
  created() { this.fetch(); },
  methods: {
    notify(text, color = 'success') { this.snackbar = { show: true, text, color }; },
    fetch() { this.loading = true; api.get('/teams').then(res => this.items = res.data).finally(() => this.loading = false); },
    openCreate() { this.editing = false; this.currentId = null; this.form = { member_name: '', position: '', bio: '', photo_url: '', facebook_link: '', linkedin_link: '', twitter_link: '', is_active: true }; this.dialog = true; },
    openEdit(item) { this.editing = true; this.currentId = item.id; this.form = { member_name: item.member_name, position: item.position, bio: item.bio, photo_url: item.photo_url, facebook_link: item.facebook_link, linkedin_link: item.linkedin_link, twitter_link: item.twitter_link, is_active: item.is_active }; this.dialog = true; },
    save() {
      this.saving = true;
      const req = this.editing ? api.put(`/teams/${this.currentId}`, this.form) : api.post('/teams', this.form);
      req.then(() => { this.dialog = false; this.fetch(); this.notify('Saved successfully'); })
         .catch(() => this.notify('Save failed', 'error'))
         .finally(() => this.saving = false);
    },
    remove(item) { if (!confirm('Delete this member?')) return; api.delete(`/teams/${item.id}`).then(() => { this.fetch(); this.notify('Deleted'); }).catch(() => this.notify('Delete failed', 'error')); },
  },
};
</script>
