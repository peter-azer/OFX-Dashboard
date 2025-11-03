<template>
  <div>
    <v-row class="mb-4" align="center">
      <v-col cols="6">
        <h2>Users</h2>
      </v-col>
      <v-col cols="6" class="text-right">
        <v-btn color="primary" @click="openCreate">New User</v-btn>
      </v-col>
    </v-row>

    <v-data-table :items="items" :headers="headers" :loading="loading">
      <template #item.roles="{ item }">
        <v-chip v-for="role in item.roles" :key="role.id" size="small" class="mr-1">
          {{ role.name }}
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

    <v-dialog v-model="dialog" max-width="500">
      <v-card>
        <v-card-title>{{ editing ? 'Edit User' : 'Create User' }}</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="save">
            <v-text-field 
              v-model="form.name" 
              label="Name" 
              required 
              :error-messages="errors.name"
            />
            <v-text-field 
              v-model="form.email" 
              label="Email" 
              type="email" 
              required 
              :error-messages="errors.email"
            />
            <v-text-field 
              v-model="form.password" 
              :label="editing ? 'New Password (leave blank to keep current)' : 'Password'" 
              type="password" 
              :required="!editing"
              :error-messages="errors.password"
            />
            <v-text-field 
              v-if="!editing"
              v-model="form.password_confirmation" 
              label="Confirm Password" 
              type="password" 
              required
              :error-messages="errors.password_confirmation"
            />
            <v-select
              v-model="form.roles"
              :items="availableRoles"
              item-title="name"
              item-value="id"
              label="Roles"
              multiple
              chips
              :error-messages="errors.roles"
            />
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="dialog = false">Cancel</v-btn>
          <v-btn color="primary" :loading="saving" @click="save">Save</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="2500">
      {{ snackbar.text }}
    </v-snackbar>

    <v-dialog v-model="confirm.show" max-width="420">
      <v-card>
        <v-card-title>Confirm Delete</v-card-title>
        <v-card-text>Are you sure you want to delete this user?</v-card-text>
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
import { PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';

export default {
  name: 'Users',
  components: { PencilSquareIcon, TrashIcon },
  data() {
    return {
      loading: false,
      saving: false,
      items: [],
      dialog: false,
      editing: false,
      currentId: null,
      availableRoles: [],
      form: { 
        name: '', 
        email: '', 
        password: '',
        password_confirmation: '',
        roles: [] 
      },
      errors: {},
      confirm: { 
        show: false, 
        item: null, 
        loading: false 
      },
      snackbar: { 
        show: false, 
        text: '', 
        color: 'success' 
      },
      headers: [
        { title: 'ID', key: 'id' },
        { title: 'Name', key: 'name' },
        { title: 'Email', key: 'email' },
        { title: 'Roles', key: 'roles' },
        { title: 'Actions', key: 'actions', sortable: false },
      ],
    }
  },
  created() {
    this.fetch();
    this.fetchRoles();
  },
  methods: {
    notify(text, color = 'success') {
      this.snackbar = { show: true, text, color };
    },
    async fetch() {
      this.loading = true;
      try {
        const response = await api.get('/users');
        this.items = response.data.data;
      } catch (error) {
        this.notify('Failed to load users', 'error');
        console.error('Error fetching users:', error);
      } finally {
        this.loading = false;
      }
    },
    async fetchRoles() {
      try {
        const response = await api.get('/roles');
        this.availableRoles = response.data.data;
      } catch (error) {
        console.error('Error fetching roles:', error);
      }
    },
    openCreate() {
      this.form = { name: '', email: '', password: '', password_confirmation: '', roles: [] };
      this.errors = {};
      this.editing = false;
      this.dialog = true;
    },
    openEdit(item) {
      this.currentId = item.id;
      this.form = { 
        ...item, 
        password: '',
        password_confirmation: '',
        roles: item.roles.map(role => role.id) 
      };
      this.errors = {};
      this.editing = true;
      this.dialog = true;
    },
    async save() {
      this.saving = true;
      this.errors = {};
      
      try {
        const url = this.editing 
          ? `/users/${this.currentId}`
          : '/users';
        
        const data = { ...this.form };
        
        // Remove password fields if they're empty during edit
        if (this.editing && !data.password) {
          delete data.password;
          delete data.password_confirmation;
        }

        const response = this.editing
          ? await api.put(url, data)
          : await api.post(url, data);

        this.notify(`User ${this.editing ? 'updated' : 'created'} successfully`);
        this.dialog = false;
        this.fetch();
      } catch (error) {
        if (error.response?.status === 422) {
          // Handle validation errors
          this.errors = error.response.data.errors || {};
          this.notify('Please fix the validation errors', 'error');
        } else if (error.response?.status === 403) {
          this.notify('You do not have permission to perform this action', 'error');
        } else {
          this.notify(error.response?.data?.message || 'An error occurred', 'error');
          console.error('Error saving user:', error);
        }
      } finally {
        this.saving = false;
      }
    },
    openDelete(item) {
      this.confirm = { show: true, item };
    },
    async confirmDelete() {
      if (!this.confirm.item) return;
      
      this.confirm.loading = true;
      
      try {
        await api.delete(`/users/${this.confirm.item.id}`);
        this.notify('User deleted successfully');
        this.confirm.show = false;
        this.fetch();
      } catch (error) {
        this.notify(error.response?.data?.message || 'Failed to delete user', 'error');
        console.error('Error deleting user:', error);
      } finally {
        this.confirm.loading = false;
      }
    },
  },
};
</script>
