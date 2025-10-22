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
                                prepend-icon="mdi-account"
                                type="email"
                                required
                            ></v-text-field>
                            <v-text-field
                                v-model="password"
                                label="Password"
                                prepend-icon="mdi-lock"
                                type="password"
                                required
                            ></v-text-field>
                            <v-btn type="submit" color="primary" block>Login</v-btn>
                        </v-form>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            email: '',
            password: '',
        };
    },
    methods: {
        login() {
            axios.post('/api/admin/login', {
                email: this.email,
                password: this.password,
            }).then(response => {
                if (response.data.success) {
                    this.$router.push({ name: 'Dashboard' });
                } else {
                    alert(response.data.message);
                }
            });
        },
    },
};
</script>
