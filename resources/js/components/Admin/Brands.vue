<template>
  <div>
    <v-row class="mb-4" align="center">
      <v-col cols="6">
        <h2>Brands</h2>
      </v-col>
      <v-col cols="6" class="text-right">
        <v-btn color="primary" @click="openCreate">New Brand</v-btn>
      </v-col>
    </v-row>

    <v-data-table :items="items" :headers="headers" :loading="loading">
      <template #item.actions="{ item }">
        <v-btn size="small" variant="text" icon="mdi-pencil" @click="openEdit(item)" />
        <v-btn size="small" variant="text" color="error" icon="mdi-delete" @click="remove(item)" />
      </template>
    </v-data-table>

    <v-dialog v-model="dialog" max-width="500">
      <v-card>
        <v-card-title>{{ editing ? 'Edit Brand' : 'Create Brand' }}</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="save">
            <v-text-field v-model="form.brand_name" label="Brand Name" required />
            <v-text-field v-model="form.logo_url" label="Logo URL" required />
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
      form: { brand_name: '', logo_url: '', order: 0, is_active: true },
      headers: [
        { title: 'ID', key: 'id' },
        { title: 'Name', key: 'brand_name' },
        { title: 'Logo', key: 'logo_url' },
        { title: 'Order', key: 'order' },
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
    notify(text, color = 'success') {
      this.snackbar = { show: true, text, color };
    },
    fetch() {
      this.loading = true;
      api.get('/brands')
        .then((res) => (this.items = res.data))
        .finally(() => (this.loading = false));
    },
    openCreate() {
      this.editing = false;
      this.currentId = null;
      this.form = { brand_name: '', logo_url: '', order: 0, is_active: true };
      this.dialog = true;
    },
    openEdit(item) {
      this.editing = true;
      this.currentId = item.id;
      this.form = { brand_name: item.brand_name, logo_url: item.logo_url, order: item.order, is_active: item.is_active };
      this.dialog = true;
    },
    save() {
      this.saving = true;
      const req = this.editing ? api.put(`/brands/${this.currentId}`, this.form) : api.post('/brands', this.form);
      req
        .then(() => {
          this.dialog = false;
          this.fetch();
          this.notify('Saved successfully');
        })
        .catch(() => this.notify('Save failed', 'error'))
        .finally(() => (this.saving = false));
    },
    remove(item) {
      if (!confirm('Delete this brand?')) return;
      api.delete(`/brands/${item.id}`)
        .then(() => { this.fetch(); this.notify('Deleted'); })
        .catch(() => this.notify('Delete failed', 'error'));
    },
  },
};
</script>
