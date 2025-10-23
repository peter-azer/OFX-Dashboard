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
      <template #item.logo_url="{ item }">
        <v-img :src="item.logo_url" width="60" height="40" cover></v-img>
      </template>
      <template #item.actions="{ item }">
        <v-btn size="small" variant="text" icon="fas fa-pen" @click="openEdit(item)" />
        <v-btn size="small" variant="text" color="error" icon="fas fa-trash" @click="openDelete(item)" />
      </template>
    </v-data-table>

    <v-dialog v-model="dialog" max-width="500">
      <v-card>
        <v-card-title>{{ editing ? 'Edit Brand' : 'Create Brand' }}</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="save">
            <v-text-field v-model="form.brand_name" label="Brand Name" required />
            <v-file-input
              v-model="form.logo_url"
              label="Upload Logo"
              accept="image/*"
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

    <v-dialog v-model="confirm.show" max-width="420">
      <v-card>
        <v-card-title>Confirm Delete</v-card-title>
        <v-card-text>Are you sure you want to delete this brand?</v-card-text>
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
      form: { brand_name: '', logo_url: null, order: 0, is_active: true },
      confirm: { show: false, item: null, loading: false },
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
      this.form = { brand_name: '', logo_url: null, order: 0, is_active: true };
      this.dialog = true;
    },
    openEdit(item) {
      this.editing = true;
      this.currentId = item.id;
      this.form = { brand_name: item.brand_name, logo_url: null, order: item.order, is_active: item.is_active };
      this.dialog = true;
    },
    save() {
      this.saving = true;
      const fd = new FormData();
      fd.append('brand_name', this.form.brand_name);
      const logoFile = Array.isArray(this.form.logo_url) ? this.form.logo_url[0] : this.form.logo_url;
      if (logoFile instanceof File) fd.append('logo_url', logoFile);
      if (this.form.order !== null && this.form.order !== undefined) fd.append('order', this.form.order);
      fd.append('is_active', this.form.is_active ? '1' : '0');

      const req = this.editing
        ? (fd.append('_method', 'PUT'), api.post(`/brands/${this.currentId}`, fd))
        : api.post('/brands', fd);
      req
        .then(() => {
          this.dialog = false;
          this.fetch();
          this.notify('Saved successfully');
        })
        .catch(() => this.notify('Save failed', 'error'))
        .finally(() => (this.saving = false));
    },
    openDelete(item) { this.confirm = { show: true, item, loading: false }; },
    confirmDelete() {
      if (!this.confirm.item) return;
      this.confirm.loading = true;
      api.delete(`/brands/${this.confirm.item.id}`)
        .then(() => { this.fetch(); this.notify('Deleted'); this.confirm.show = false; })
        .catch(() => this.notify('Delete failed', 'error'))
        .finally(() => { this.confirm.loading = false; this.confirm.item = null; });
    },
  },
};
</script>
