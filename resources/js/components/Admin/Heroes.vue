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
      <template #item.actions="{ item }">
        <v-btn size="small" variant="text" icon="mdi-pencil" @click="openEdit(item)" />
        <v-btn size="small" variant="text" color="error" icon="mdi-delete" @click="remove(item)" />
      </template>
    </v-data-table>

    <v-dialog v-model="dialog" max-width="600">
      <v-card>
        <v-card-title>{{ editing ? 'Edit Hero' : 'Create Hero' }}</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="save">
            <v-text-field v-model="form.title" label="Title" required />
            <v-text-field v-model="form.subtitle" label="Subtitle" required />
            <v-text-field v-model="form.button_text" label="Button Text" required />
            <v-text-field v-model="form.button_link" label="Button Link" required />
            <v-text-field v-model="form.image_url" label="Image URL" />
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
  </div>
</template>

<script>
import api from '../../api';

export default {
  data() {
    return {
      loading: false,
      saving: false,
      heroes: [],
      dialog: false,
      editing: false,
      currentId: null,
      form: {
        title: '',
        subtitle: '',
        button_text: '',
        button_link: '',
        image_url: '',
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
    };
  },
  created() {
    this.fetch();
  },
  methods: {
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
      this.form = { title: '', subtitle: '', button_text: '', button_link: '', image_url: '', order: 0, is_active: true };
      this.dialog = true;
    },
    openEdit(item) {
      this.editing = true;
      this.currentId = item.id;
      this.form = {
        title: item.title,
        subtitle: item.subtitle,
        button_text: item.button_text,
        button_link: item.button_link,
        image_url: item.image_url,
        order: item.order,
        is_active: item.is_active,
      };
      this.dialog = true;
    },
    save() {
      this.saving = true;
      const payload = { ...this.form };
      const req = this.editing
        ? api.put(`/heroes/${this.currentId}`, payload)
        : api.post('/heroes', payload);
      req
        .then(() => {
          this.dialog = false;
          this.fetch();
        })
        .catch((err) => {
          const msg = err?.response?.data?.message || 'Save failed';
          alert(msg);
        })
        .finally(() => (this.saving = false));
    },
    remove(item) {
      if (!confirm('Delete this hero?')) return;
      api
        .delete(`/heroes/${item.id}`)
        .then(() => this.fetch())
        .catch(() => alert('Delete failed'));
    },
  },
};
</script>
