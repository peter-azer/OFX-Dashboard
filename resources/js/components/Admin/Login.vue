<template>
    <v-container class="fill-height" fluid>
        <v-row align="center" justify="center">
            <v-col cols="12" sm="8" md="4">
                <v-card class="elevation-12">
                    <v-toolbar color="primary" dark flat>
                        <v-toolbar-title>Login</v-toolbar-title>
                    </v-toolbar>
                    <v-card-text>
                        <v-form @submit.prevent="login">
                            <v-text-field
                                v-model="email"
                                label="Email"
                                
                                type="email"
                                required
                            >
                                <template #prepend>
                                    <UserIcon class="h-5 w-5" />
                                </template>
                            </v-text-field>
                            <v-text-field
                                v-model="password"
                                label="Password"
                                
                                type="password"
                                required
                            >
                                <template #prepend>
                                    <LockClosedIcon class="h-5 w-5" />
                                </template>
                            </v-text-field>
                            <v-btn type="submit" color="primary" block :loading="loading">Login</v-btn>
                        </v-form>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
        <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="2500">{{ snackbar.text }}</v-snackbar>
    </v-container>
</template>

<script>
import api from '../../api';
import { UserIcon, LockClosedIcon } from '@heroicons/vue/24/outline';

export default {
    components: { UserIcon, LockClosedIcon },
    data() {
        return {
            email: '',
            password: '',
            loading: false,
            snackbar: { show: false, text: '', color: 'success' },
        };
    },
    methods: {
        notify(text, color = 'success') { this.snackbar = { show: true, text, color }; },
        login() {
            this.loading = true;
            api.post('/login', {
                email: this.email,
                password: this.password,
            })
                .then((response) => {
                    const token = response.data?.access_token;
                    if (token) {
                        localStorage.setItem('access_token', token);
                        this.notify('Login successful');
                        this.$router.push({ name: 'admin.dashboard' });
                    } else {
                        this.notify('Login failed: no token received', 'error');
                    }
                })
                .catch((err) => {
                    const msg = err?.response?.data?.message || 'Invalid login details';
                    this.notify(msg, 'error');
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>
