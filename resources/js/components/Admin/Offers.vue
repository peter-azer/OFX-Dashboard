<template>
  <div>
    <v-row class="mb-4" align="center">
      <v-col cols="6">
        <h2>Offers</h2>
      </v-col>
      <v-col cols="6" class="text-right">
        <v-btn color="primary" @click="openCreate">New Offer</v-btn>
      </v-col>
    </v-row>

    <v-data-table :items="items" :headers="headers" :loading="loading">
      <template #item.image_url="{ item }">
        <v-img :src="item.image_url" width="60" height="40" cover></v-img>
      </template>
      <template #item.is_active="{ item }">
        <v-chip :color="item.is_active ? 'success' : 'grey'" size="small">
          {{ item.is_active ? 'Active' : 'Inactive' }}
        </v-chip>
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

    <v-dialog v-model="dialog" max-width="700">
      <v-card>
        <v-card-title>{{ editing ? 'Edit Offer' : 'Create Offer' }}</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="save">
            <v-text-field v-model="form.offer_name" label="Offer Name" required />
            <v-text-field v-model="form.offer_name_ar" label="Offer Name (AR)" required />
            <v-textarea v-model="form.short_description" label="Short Description" required rows="3" />
            <v-textarea v-model="form.short_description_ar" label="Short Description (AR)" required rows="3" />
            <v-file-input
              v-model="form.image_url"
              label="Upload Image"
              accept="image/*,image/svg+xml"
              show-size
              clearable
              :required="!editing"
            >
              <template #prepend>
                <PhotoIcon class="h-5 w-5" />
              </template>
            </v-file-input>
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
        <v-card-text>Are you sure you want to delete this offer?</v-card-text>
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
      form: { 
        offer_name: '',
        offer_name_ar: '',
        short_description: '',
        short_description_ar: '',
        image_url: null,
        order: 0,
        is_active: true 
      },
      headers: [
        { title: 'ID', key: 'id' },
        { title: 'Name', key: 'offer_name' },
        { title: 'Image', key: 'image_url' },
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
      api.get('/offers')
        .then(res => this.items = res.data)
        .finally(() => this.loading = false);
    },
    openCreate() {
      this.editing = false; this.currentId = null;
      this.form = { offer_name: '', offer_name_ar: '', short_description: '', short_description_ar: '', image_url: null, order: 0, is_active: true };
      this.dialog = true;
    },
    openEdit(item) {
      this.editing = true; this.currentId = item.id;
      this.form = { 
        offer_name: item.offer_name,
        offer_name_ar: item.offer_name_ar,
        short_description: item.short_description,
        short_description_ar: item.short_description_ar,
        image_url: null,
        order: item.order,
        is_active: item.is_active 
      };
      this.dialog = true;
    },
    save() {
      this.saving = true;
      const fd = new FormData();
      fd.append('offer_name', this.form.offer_name);
      fd.append('offer_name_ar', this.form.offer_name_ar);
      fd.append('short_description', this.form.short_description);
      fd.append('short_description_ar', this.form.short_description_ar);
      const imgFile = Array.isArray(this.form.image_url) ? this.form.image_url[0] : this.form.image_url;
      if (imgFile instanceof File) fd.append('image_url', imgFile);
      if (this.form.order !== null && this.form.order !== undefined) fd.append('order', this.form.order);
      fd.append('is_active', this.form.is_active ? '1' : '0');

      const req = this.editing
        ? (fd.append('_method', 'PUT'), api.post(`/offers/${this.currentId}`, fd))
        : api.post('/offers', fd);
      req.then(() => { this.dialog = false; this.fetch(); this.notify('Saved successfully'); })
         .catch(() => this.notify('Save failed', 'error'))
         .finally(() => this.saving = false);
    },
    openDelete(item) { this.confirm = { show: true, item, loading: false }; },
    confirmDelete() {
      if (!this.confirm.item) return;
      this.confirm.loading = true;
      api.delete(`/offers/${this.confirm.item.id}`)
        .then(() => { this.fetch(); this.notify('Deleted'); this.confirm.show = false; })
        .catch(() => this.notify('Delete failed', 'error'))
        .finally(() => { this.confirm.loading = false; this.confirm.item = null; });
    },
  },
};
</script>
