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

    <v-data-table :items="items" :headers="headers" :loading="loading" v-model:sort-by="sortBy">
      <template #item.project_image="{ item }">
        <v-img :src="item.project_image" width="60" height="40" cover></v-img>
      </template>
      <template #item.service_name="{ item }">
        {{ item.service?.service_name || '-' }}
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
        <v-card-title>{{ editing ? 'Edit Work' : 'Create Work' }}</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="save">
            <v-text-field v-model="form.project_title" label="Project Title" required />
            <v-text-field v-model="form.project_title_ar" label="Project Title (AR)" required />
            <v-textarea v-model="form.project_description" label="Project Description" required rows="4" />
            <v-textarea v-model="form.project_description_ar" label="Project Description (AR)" required rows="4" />
            <v-file-input v-model="form.project_image" label="Upload Project Image" accept="image/*" show-size clearable
              :required="!editing">
              <template #prepend>
                <PhotoIcon class="h-5 w-5" />
              </template>
            </v-file-input>

            <!-- Gallery Images input -->
            <v-file-input v-model="form.project_images" label="Upload Gallery Images" accept="image/*"
              prepend-icon="mdi-image-multiple" multiple show-size clearable />

            <!-- Thumbnails: preview selected gallery images -->
            <div class="flex flex-wrap gap-2 my-2">
              <!-- New selected images -->
              <div v-for="(img, idx) in form.project_images" :key="idx" class="inline-block" v-if="img">
                <img :src="img instanceof File ? URL.createObjectURL(img) : img"
                  style="width:60px;height:40px;object-fit:cover;border-radius:4px;" />
              </div>
              <!-- Existing images from backend (editing only) -->
              <div v-if="editing && form.images && form.images.length" v-for="(img, i) in form.images"
                :key="'stored' + i">
                <img :src="img.image_url"
                  style="width:60px;height:40px;object-fit:cover;border-radius:4px;opacity:0.6" />
              </div>
            </div>
            <v-text-field v-model="form.project_link" label="Project Link" />
            <v-text-field v-model.number="form.order" type="number" label="Order" />
            <v-select v-model="form.service_id" :items="services" item-title="service_name" item-value="id"
              label="Service" required />
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
import { PencilSquareIcon, TrashIcon, PhotoIcon } from '@heroicons/vue/24/outline';

export default {
  components: { PencilSquareIcon, TrashIcon, PhotoIcon },
  data() {
    return {
      loading: false,
      saving: false,
      items: [],
      services: [],
      dialog: false,
      editing: false,
      currentId: null,
      form: { project_title: '', project_title_ar: '', project_description: '', project_description_ar: '', project_image: null, project_link: '', order: 0, service_id: null, is_active: true, project_images: [], images: [] },
      confirm: { show: false, item: null, loading: false },
      headers: [
        { title: 'ID', key: 'id' },
        { title: 'Title', key: 'project_title' },
        { title: 'Image', key: 'project_image', sortable: false },
        { title: 'Order', key: 'order' },
        { title: 'Service', key: 'service_name' },
        { title: 'Active', key: 'is_active' },
        { title: 'Actions', key: 'actions', sortable: false },
      ],
      snackbar: { show: false, text: '', color: 'success' },
      sortBy: [{ key: 'order', order: 'asc' }],
    };
  },
  created() { this.fetch(); this.fetchServices(); },
  watch: {
    sortBy: {
      handler() {
        this.fetch();
      },
      deep: true,
    }
  },
  methods: {
    notify(text, color = 'success') { this.snackbar = { show: true, text, color }; },
    fetch() {
      this.loading = true;
      const firstSort = Array.isArray(this.sortBy) && this.sortBy.length ? this.sortBy[0] : { key: 'id', order: 'asc' };
      const params = { sort_by: firstSort.key, sort_dir: firstSort.order };
      api.get('/works', { params })
        .then(res => {
          // Ensure service_name exists for table binding
          this.items = Array.isArray(res.data)
            ? res.data.map(it => ({
              ...it,
              service_name: it?.service?.service_name || ''
            }))
            : [];
        })
        .finally(() => (this.loading = false));
    },
    fetchServices() { api.get('/services').then(res => this.services = res.data); },
    openCreate() { this.editing = false; this.currentId = null; this.form = { project_title: '', project_title_ar: '', project_description: '', project_description_ar: '', project_image: null, project_link: '', order: 0, service_id: null, is_active: true, project_images: [], images: [] }; this.dialog = true; },
    openEdit(item) {
      this.editing = true;
      this.currentId = item.id;
      this.form = {
        project_title: item.project_title,
        project_title_ar: item.project_title_ar,
        project_description: item.project_description,
        project_description_ar: item.project_description_ar,
        project_image: null,
        project_link: item.project_link,
        order: typeof item.order === 'number' ? item.order : 0,
        service_id: item.service_id || item.service?.id || null,
        is_active: item.is_active,
        project_images: [],
        images: item.images || [],
      };
      this.dialog = true;
    },
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
      if (this.form.order !== null && this.form.order !== undefined) fd.append('order', String(this.form.order));
      if (this.form.service_id) fd.append('service_id', String(this.form.service_id));
      fd.append('is_active', this.form.is_active ? '1' : '0');

      // Gallery image uploads
      if (this.form.project_images && Array.isArray(this.form.project_images)) {
        this.form.project_images.forEach((file, idx) => {
          if (file instanceof File) {
            fd.append('project_images[]', file);
          }
        });
      }

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
