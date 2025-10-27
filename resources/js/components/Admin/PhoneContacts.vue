<template>
  <div>
    <v-row class="mb-4" align="center">
      <v-col cols="6">
        <h2>Phone Contacts</h2>
      </v-col>
      <v-col cols="6" class="text-right">
        <v-btn color="primary" @click="openCreate">New Contact</v-btn>
      </v-col>
    </v-row>

    <v-data-table :items="items" :headers="headers" :loading="loading">
      <template #item.actions="{ item }">
        <v-btn size="small" variant="text" icon="fas fa-pen" @click="openEdit(item)" />
        <v-btn size="small" variant="text" color="error" icon="fas fa-trash" @click="openDelete(item)" />
      </template>
    </v-data-table>

    <v-dialog v-model="dialog" max-width="600">
      <v-card>
        <v-card-title>{{ editing ? 'Edit Contact' : 'Create Contact' }}</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="save">
            <v-text-field v-model="form.name" label="Name" required />
            <v-text-field v-model="form.phone" label="Phone" required />
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
        <v-card-text>Are you sure you want to delete this contact?</v-card-text>
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
      form: { name: '', phone: '' },
      confirm: { show: false, item: null, loading: false },
      headers: [
        { title: 'ID', key: 'id' },
        { title: 'Name', key: 'name' },
        { title: 'Phone', key: 'phone' },
        { title: 'Actions', key: 'actions', sortable: false },
      ],
      snackbar: { show: false, text: '', color: 'success' },
    };
  },
  created() { this.fetch(); },
  methods: {
    notify(text, color = 'success') { this.snackbar = { show: true, text, color }; },
    fetch() { this.loading = true; api.get('/phone-contacts').then(res => this.items = res.data).finally(() => this.loading = false); },
    openCreate() { this.editing = false; this.currentId = null; this.form = { name: '', phone: '' }; this.dialog = true; },
    openEdit(item) { this.editing = true; this.currentId = item.id; this.form = { name: item.name, phone: item.phone }; this.dialog = true; },
    save() {
      this.saving = true;
      const payload = { name: this.form.name, phone: this.form.phone };
      const req = this.editing
        ? api.put(`/phone-contacts/${this.currentId}`, payload)
        : api.post('/phone-contacts', payload);
      req.then(() => { this.dialog = false; this.fetch(); this.notify('Saved successfully'); })
         .catch(() => this.notify('Save failed', 'error'))
         .finally(() => this.saving = false);
    },
    openDelete(item) { this.confirm = { show: true, item, loading: false }; },
    confirmDelete() {
      if (!this.confirm.item) return;
      this.confirm.loading = true;
      api.delete(`/phone-contacts/${this.confirm.item.id}`)
        .then(() => { this.fetch(); this.notify('Deleted'); this.confirm.show = false; })
        .catch(() => this.notify('Delete failed', 'error'))
        .finally(() => { this.confirm.loading = false; this.confirm.item = null; });
    },
  },
};
</script>
