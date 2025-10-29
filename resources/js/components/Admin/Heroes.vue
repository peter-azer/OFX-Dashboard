<template>
  <div>
    <v-row class="mb-4" align="center">
      <v-col cols="6">
        <h2>Heroes</h2>
      </v-col>
      <v-col cols="6" class="text-right">
        <v-btn color="primary" @click="openCreate">New Hero</v-btn>
      </v-col>
    </v-row>

    <v-data-table :items="heroes" :headers="headers" :loading="loading">
      <template #item.image_url="{ item }">
        <v-img :src="item.image_url" width="60" height="40" cover></v-img>
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

    <v-dialog v-model="dialog" max-width="600">
      <v-card>
        <v-card-title>{{ editing ? 'Edit Hero' : 'Create Hero' }}</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="save">
            <v-text-field v-model="form.title" label="Title" required />
            <v-text-field v-model="form.title_ar" label="Title (AR)" required />
            <v-text-field v-model="form.subtitle" label="Subtitle" required />
            <v-text-field v-model="form.subtitle_ar" label="Subtitle (AR)" required />
            <v-text-field v-model="form.button_text" label="Button Text" required />
            <v-text-field v-model="form.button_text_ar" label="Button Text (AR)" required />
            <v-text-field v-model="form.button_link" label="Button Link" required />
            <v-file-input
              v-model="form.image_url"
              label="Upload Image"
              accept="image/*"
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
        <v-card-text>Are you sure you want to delete this hero?</v-card-text>
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
      heroes: [],
      dialog: false,
      editing: false,
      currentId: null,
      confirm: { show: false, item: null, loading: false },
      form: {
        title: '',
        title_ar: '',
        subtitle: '',
        subtitle_ar: '',
        button_text: '',
        button_text_ar: '',
        button_link: '',
        image_url: null,
        order: 0,
        is_active: true,
      },
      headers: [
        { title: 'ID', key: 'id' },
        { title: 'Title', key: 'title' },
        { title: 'Subtitle', key: 'subtitle' },
        { title: 'Button Text', key: 'button_text' },
        { title: 'Button Link', key: 'button_link' },
        { title: 'Image', key: 'image_url' },
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
    notify(text, color = 'success') { this.snackbar = { show: true, text, color }; },
    fetch() {
      this.loading = true;
      api
        .get('/heroes')
        .then((res) => {
          this.heroes = res.data;
        })
        .finally(() => (this.loading = false));
    },
    openCreate() {
      this.editing = false;
      this.currentId = null;
      this.form = { title: '', title_ar: '', subtitle: '', subtitle_ar: '', button_text: '', button_text_ar: '', button_link: '', image_url: null, order: 0, is_active: true };
      this.dialog = true;
    },
    openEdit(item) {
      this.editing = true;
      this.currentId = item.id;
      this.form = {
        title: item.title,
        title_ar: item.title_ar,
        subtitle: item.subtitle,
        subtitle_ar: item.subtitle_ar,
        button_text: item.button_text,
        button_text_ar: item.button_text_ar,
        button_link: item.button_link,
        image_url: null,
        order: item.order,
        is_active: item.is_active,
      };
      this.dialog = true;
    },
    save() {
      this.saving = true;
      const fd = new FormData();
      fd.append('title', this.form.title);
      fd.append('title_ar', this.form.title_ar);
      fd.append('subtitle', this.form.subtitle);
      fd.append('subtitle_ar', this.form.subtitle_ar);
      fd.append('button_text', this.form.button_text);
      fd.append('button_text_ar', this.form.button_text_ar);
      fd.append('button_link', this.form.button_link);
      const imgFile = Array.isArray(this.form.image_url) ? this.form.image_url[0] : this.form.image_url;
      if (imgFile instanceof File) { fd.append('image_url', imgFile); }
      if (this.form.order !== null && this.form.order !== undefined) fd.append('order', this.form.order);
      fd.append('is_active', this.form.is_active ? '1' : '0');

      const req = this.editing
        ? (fd.append('_method', 'PUT'), api.post(`/heroes/${this.currentId}`, fd))
        : api.post('/heroes', fd);
      req
        .then(() => {
          this.dialog = false;
          this.fetch();
        })
        .catch((err) => {
          const msg = err?.response?.data?.message || 'Save failed';
          this.notify(msg, 'error');
        })
        .finally(() => (this.saving = false));
    },
    openDelete(item) {
      this.confirm = { show: true, item, loading: false };
    },
    confirmDelete() {
      if (!this.confirm.item) return;
      this.confirm.loading = true;
      api
        .delete(`/heroes/${this.confirm.item.id}`)
        .then(() => { this.fetch(); this.notify('Deleted'); this.confirm.show = false; })
        .catch(() => this.notify('Delete failed', 'error'))
        .finally(() => { this.confirm.loading = false; this.confirm.item = null; });
    },
  },
};
</script>
