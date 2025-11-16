<template>
    <v-card>
        <v-card-title>
            Form Submissions
            <v-spacer></v-spacer>
            <v-text-field v-model="search" append-icon="mdi-magnify" label="Search" single-line hide-details
                class="mb-4"></v-text-field>
        </v-card-title>
        <v-data-table :headers="headers" :items="submissions" :search="search" :loading="loading" :items-per-page="10"
            class="elevation-1">
            <template v-slot:item.services="{ item }">
                <v-chip v-for="(service, index) in item.services" :key="index" small class="mr-1 mb-1">
                    {{ service.service_name }}
                </v-chip>
            </template>
            <template v-slot:item.created_at="{ item }">
                {{ formatDate(item.created_at) }}
            </template>
            <template v-slot:item.actions="{ item }">
                <v-icon small class="mr-2" @click="viewItem(item)">
                    mdi-eye
                </v-icon>
                <v-icon small @click="deleteItem(item)">
                    mdi-delete
                </v-icon>
            </template>
        </v-data-table>

        <!-- View Dialog -->
        <v-dialog v-model="dialog" max-width="600">
            <v-card>
                <v-card-title>Submission Details</v-card-title>
                <v-card-text>
                    <v-list>
                        <v-list-item>
                            <v-list-item-content>
                                <v-list-item-title>Name</v-list-item-title>
                                <v-list-item-subtitle>{{ selectedItem.full_name }}</v-list-item-subtitle>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-content>
                                <v-list-item-title>Email</v-list-item-title>
                                <v-list-item-subtitle>{{ selectedItem.email }}</v-list-item-subtitle>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-content>
                                <v-list-item-title>Phone</v-list-item-title>
                                <v-list-item-subtitle>{{ selectedItem.phone_number }}</v-list-item-subtitle>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-content>
                                <v-list-item-title>Message</v-list-item-title>
                                <v-list-item-subtitle>{{ selectedItem.message || 'No message provided'
                                    }}</v-list-item-subtitle>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item v-if="selectedItem.services && selectedItem.services.length > 0">
                            <v-list-item-content>
                                <v-list-item-title>Services</v-list-item-title>
                                <v-chip-group>
                                    <v-chip v-for="(service, index) in selectedItem.services" :key="index" small
                                        class="mr-1 mb-1">
                                        {{ service.service_name }}
                                    </v-chip>
                                </v-chip-group>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-content>
                                <v-list-item-title>Submitted On</v-list-item-title>
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
                    Are you sure you want to delete this submission?
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
    name: 'FormSubmissions',
    data() {
        return {
            search: '',
            loading: true,
            submissions: [],
            selectedItem: {},
            dialog: false,
            deleteDialog: false,
            itemToDelete: null,
            headers: [
                { text: 'Name', value: 'full_name' },
                { text: 'Email', value: 'email' },
                { text: 'Phone', value: 'phone_number' },
                { text: 'Services', value: 'services', sortable: false },
                { text: 'Submitted On', value: 'created_at' },
                { text: 'Actions', value: 'actions', sortable: false, align: 'end' },
            ],
        };
    },
    created() {
        this.fetchSubmissions();
    },
    methods: {
        async fetchSubmissions() {
            try {
                this.loading = true;
                const response = await axios.get('/api/form-submissions');
                this.submissions = response.data;
            } catch (error) {
                console.error('Error fetching submissions:', error);
                this.$toast.error('Failed to load form submissions');
            } finally {
                this.loading = false;
            }
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
                await axios.delete(`/api/form-submissions/${this.itemToDelete}`);
                this.submissions = this.submissions.filter(item => item.id !== this.itemToDelete);
                this.$toast.success('Submission deleted successfully');
            } catch (error) {
                console.error('Error deleting submission:', error);
                this.$toast.error('Failed to delete submission');
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
                minute: '2-digit'
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
