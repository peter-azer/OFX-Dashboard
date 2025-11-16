<template>
  <v-card>
    <v-card-title>
      Phone Records
      <v-spacer></v-spacer>
      <v-text-field
        v-model="search"
        append-icon="mdi-magnify"
        label="Search"
        single-line
        hide-details
        class="mb-4"
      ></v-text-field>
    </v-card-title>
    <v-data-table
      :headers="headers"
      :items="recordsWithContactInfo"
      :search="search"
      :loading="loading"
      :items-per-page="20"
      class="elevation-1"
    >
      <template v-slot:item.created_at="{ item }">
        {{ formatDate(item.created_at) }}
      </template>
      <template v-slot:item.actions="{ item }">
        <v-icon
          small
          class="mr-2"
          @click="viewItem(item)"
        >
          mdi-eye
        </v-icon>
        <v-icon
          small
          @click="deleteItem(item)"
        >
          mdi-delete
        </v-icon>
      </template>
    </v-data-table>

    <!-- View Dialog -->
    <v-dialog v-model="dialog" max-width="600">
      <v-card>
        <v-card-title>Record Details</v-card-title>
        <v-card-text>
          <v-list>
            <v-list-item>
              <v-list-item-content>
                <v-list-item-title>Contact Name</v-list-item-title>
                <v-list-item-subtitle>{{ selectedItem.contact?.name || 'N/A' }}</v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
            <v-list-item>
              <v-list-item-content>
                <v-list-item-title>Phone Number</v-list-item-title>
                <v-list-item-subtitle>{{ selectedItem.contact?.phone || 'N/A' }}</v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
            <v-list-item>
              <v-list-item-content>
                <v-list-item-title>Recorded At</v-list-item-title>
                <v-list-item-subtitle>{{ formatDate(selectedItem.created_at) }}</v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" text @click="dialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title>Confirm Delete</v-card-title>
        <v-card-text>
          Are you sure you want to delete this record?
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="deleteDialog = false">Cancel</v-btn>
          <v-btn color="error" @click="confirmDelete">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-card>
</template>

<script>
import axios from 'axios';

export default {
  name: 'PhoneRecords',
  data() {
    return {
      search: '',
      loading: true,
      records: [],
      contacts: [],
      selectedItem: {},
      dialog: false,
      deleteDialog: false,
      itemToDelete: null,
      headers: [
        { text: 'Contact Name', value: 'contact_name' },
        { text: 'Phone Number', value: 'contact_phone' },
        { text: 'Recorded At', value: 'created_at' },
        { text: 'Actions', value: 'actions', sortable: false, align: 'end' },
      ],
    };
  },
  computed: {
    recordsWithContactInfo() {
      return this.records.map(record => ({
        ...record,
        contact_name: this.getContactName(record.phone_contacts_id),
        contact_phone: this.getContactPhone(record.phone_contacts_id),
        contact: this.getContact(record.phone_contacts_id)
      }));
    }
  },
  created() {
    this.fetchData();
  },
  methods: {
    async fetchData() {
      try {
        this.loading = true;
        // Get the authentication token from localStorage
        const token = localStorage.getItem('auth_token') || '';
        
        // Common headers for all requests
        const headers = {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        };
        
        // Fetch records and contacts in parallel with authentication
        const [recordsRes, contactsRes] = await Promise.all([
          axios.get('/api/phone-records', { headers }),
          axios.get('/api/phone-contacts', { headers })
        ]);
        
        this.records = recordsRes.data;
        this.contacts = contactsRes.data;
      } catch (error) {
        console.error('Error fetching data:', error);
        if (error.response && error.response.status === 401) {
          // Handle unauthorized error - redirect to login or show login modal
          this.$toast.error('Session expired. Please login again.');
          this.$inertia.visit('/login');
        } else {
          this.$toast.error('Failed to load phone records');
        }
      } finally {
        this.loading = false;
      }
    },
    getContact(contactId) {
      return this.contacts.find(c => c.id === contactId) || null;
    },
    getContactName(contactId) {
      const contact = this.getContact(contactId);
      return contact ? contact.name : 'Unknown';
    },
    getContactPhone(contactId) {
      const contact = this.getContact(contactId);
      return contact ? contact.phone : 'N/A';
    },
    viewItem(item) {
      this.selectedItem = { ...item };
      this.dialog = true;
    },
    deleteItem(item) {
      this.itemToDelete = item.id;
      this.deleteDialog = true;
    },
    async confirmDelete() {
      if (!this.itemToDelete) return;
      
      try {
        const token = localStorage.getItem('auth_token') || '';
        await axios.delete(`/api/phone-records/${this.itemToDelete}`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });
        this.records = this.records.filter(item => item.id !== this.itemToDelete);
        this.$toast.success('Record deleted successfully');
      } catch (error) {
        console.error('Error deleting record:', error);
        if (error.response && error.response.status === 401) {
          this.$toast.error('Session expired. Please login again.');
          this.$inertia.visit('/login');
        } else {
          this.$toast.error('Failed to delete record');
        }
      } finally {
        this.deleteDialog = false;
        this.itemToDelete = null;
      }
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      };
      return new Date(dateString).toLocaleDateString(undefined, options);
    },
  },
};
</script>

<style scoped>
.v-data-table {
  margin-top: 1rem;
}
</style>
