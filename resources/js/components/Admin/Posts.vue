<template>
    <div>
        <v-row class="mb-4" align="center">
            <v-col cols="6">
                <h2>Posts</h2>
            </v-col>
            <v-col cols="6" class="text-right">
                <v-btn color="primary" @click="openCreate">New Post</v-btn>
            </v-col>
        </v-row>

        <v-data-table :items="items" :headers="headers" :loading="loading">
            <template #item.image_url="{ item }">
                <v-img v-if="item.image_url" :src="item.image_url" width="60" height="60" cover></v-img>
            </template>
            <template #item.status="{ item }">
                <v-chip size="small" :color="item.status === 'publish' ? 'green' : 'grey'" variant="flat">
                    {{ item.status }}
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

        <v-dialog v-model="dialog" max-width="800">
            <v-card>
                <v-card-title>{{ editing ? 'Edit Post' : 'Create Post' }}</v-card-title>
                <v-card-text>
                    <v-form @submit.prevent="save">
                        <v-text-field v-model="form.title" label="Title" required />
                        <v-textarea v-model="form.excerpt" label="Excerpt" rows="2" auto-grow />
                        <div class="mb-4">
                            <label class="text-caption mb-1 d-block">Content</label>
                            <textarea id="post-content-editor" :value="form.content"></textarea>
                        </div>
                        <v-file-input v-model="form.image_url" label="Featured Image" accept="image/*,image/svg+xml"
                            show-size clearable :required="!editing" />
                        <v-select v-model="form.status" :items="statusOptions" label="Status" item-title="text"
                            item-value="value" :menu-props="{ zIndex: 9999, location: 'bottom start' }" />
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="dialog = false">Cancel</v-btn>
                    <v-btn color="primary" :loading="saving" @click="save">Save</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="confirm.show" max-width="420">
            <v-card>
                <v-card-title>Confirm Delete</v-card-title>
                <v-card-text>Are you sure you want to delete this post?</v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="confirm.show = false">Cancel</v-btn>
                    <v-btn color="error" :loading="confirm.loading" @click="confirmDelete">Delete</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="2500">{{ snackbar.text }}</v-snackbar>
    </div>
</template>

<script>
import api from '../../api';
import { PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';

export default {
    name: 'Posts',
    components: { PencilSquareIcon, TrashIcon },
    data() {
        return {
            loading: false,
            saving: false,
            items: [],
            dialog: false,
            editing: false,
            currentId: null,
            form: { title: '', excerpt: '', content: '', image_url: null, status: 'publish' },
            statusOptions: [
                { text: 'Publish', value: 'publish' },
                { text: 'Draft', value: 'draft' },
            ],
            headers: [
                { title: 'ID', key: 'id' },
                { title: 'Image', key: 'image_url' },
                { title: 'Title', key: 'title' },
                { title: 'Slug', key: 'slug' },
                { title: 'Status', key: 'status' },
                { title: 'Actions', key: 'actions', sortable: false },
            ],
            snackbar: { show: false, text: '', color: 'success' },
            confirm: { show: false, item: null, loading: false },
        };
    },
    created() { this.fetch(); },
    watch: {
        dialog(newVal) {
            if (newVal) {
                this.$nextTick(() => this.initEditor());
            } else {
                this.destroyEditor();
            }
        },
    },
    methods: {
        notify(text, color = 'success') { this.snackbar = { show: true, text, color }; },
        initEditor() {
            if (!window.tinymce) return;
            const existing = window.tinymce.get('post-content-editor');
            if (existing) existing.remove();
            window.tinymce.init({
                selector: '#post-content-editor',
                height: 400,
                menubar: false,
                plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table wordcount',
                toolbar: 'undo redo | blocks | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | link image media | code',
                setup: (editor) => {
                    editor.on('init', () => {
                        editor.setContent(this.form.content || '');
                    });
                    editor.on('change keyup undo redo', () => {
                        this.form.content = editor.getContent();
                    });
                },
            });
        },
        destroyEditor() {
            const ed = window.tinymce?.get('post-content-editor');
            if (ed) ed.remove();
        },
        async fetch() {
            this.loading = true;
            try {
                const res = await api.get('/posts');
                this.items = Array.isArray(res.data) ? res.data : (res.data?.data || []);
            } catch (e) {
                this.notify('Failed to load posts', 'error');
                console.error(e);
            } finally {
                this.loading = false;
            }
        },
        openCreate() {
            this.editing = false; this.currentId = null; this.form = { title: '', excerpt: '', content: '', image_url: null, status: 'publish' }; this.dialog = true;
        },
        openEdit(item) {
            this.editing = true; this.currentId = item.id; this.form = { title: item.title, excerpt: item.excerpt, content: item.content, image_url: null, status: item.status || 'publish' }; this.dialog = true;
        },
        async save() {
            this.saving = true;
            try {
                const ed = window.tinymce?.get('post-content-editor');
                if (ed) this.form.content = ed.getContent();
                const fd = new FormData();
                fd.append('title', this.form.title);
                fd.append('excerpt', this.form.excerpt || '');
                fd.append('content', this.form.content);
                fd.append('status', this.form.status || 'publish');
                const imgFile = Array.isArray(this.form.image_url) ? this.form.image_url[0] : this.form.image_url;
                if (imgFile instanceof File) fd.append('image_url', imgFile);

                const req = this.editing
                    ? (fd.append('_method', 'PUT'), api.post(`/posts/${this.currentId}`, fd))
                    : api.post('/posts', fd);
                await req;
                this.dialog = false; this.fetch(); this.notify('Saved successfully');
            } catch (e) {
                this.notify('Save failed', 'error');
            } finally {
                this.saving = false;
            }
        },
        openDelete(item) { this.confirm = { show: true, item, loading: false }; },
        async confirmDelete() {
            if (!this.confirm.item) return;
            this.confirm.loading = true;
            try {
                await api.delete(`/posts/${this.confirm.item.id}`);
                this.notify('Deleted'); this.confirm.show = false; this.fetch();
            } catch (e) {
                this.notify('Delete failed', 'error');
            } finally {
                this.confirm.loading = false; this.confirm.item = null;
            }M
        },
    },
};
</script>

<style>
/* Raise TinyMCE modal dialogs, dropdowns, tooltips, colorpickers... */
.tox-tinymce-aux,
.tox-fullscreen,
.tox-dialog,
.tox-pop,
.tox-pop__dialog,
.tox-toolbar__overflow,
.tox-menu,
.tox-menu__content,
.tox-swatches {
  z-index: 99999 !important;
}

/* Editor inside dialog but under the Vuetify app bar etc. */
.tox-editor-container {
  z-index: 1000 !important;
}

</style>
