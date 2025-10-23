<template>
  <v-layout>
    <v-app-bar color="primary" prominent>
      <v-app-bar-nav-icon variant="text" @click.stop="drawer = !drawer" />
      <v-toolbar-title>Admin Panel</v-toolbar-title>
      <v-spacer />
      <v-btn icon="mdi-logout" @click="logout" :loading="loading" variant="text" />
    </v-app-bar>

    <v-navigation-drawer v-model="drawer" permanent>
      <v-list-item title="OFX Dashboard" subtitle="Content Manager" />
      <v-divider />
      <v-list density="compact" nav>
        <v-list-item :to="{ name: 'admin.dashboard' }" prepend-icon="mdi-view-dashboard" title="Dashboard" />
        <v-list-item :to="{ name: 'admin.heroes' }" prepend-icon="mdi-star" title="Heroes" />
        <v-list-item :to="{ name: 'admin.brands' }" prepend-icon="mdi-alpha-b-box" title="Brands" />
        <v-list-item :to="{ name: 'admin.abouts' }" prepend-icon="mdi-information" title="Abouts" />
        <v-list-item :to="{ name: 'admin.services' }" prepend-icon="mdi-briefcase" title="Services" />
        <v-list-item :to="{ name: 'admin.works' }" prepend-icon="mdi-briefcase-check" title="Works" />
        <v-list-item :to="{ name: 'admin.teams' }" prepend-icon="mdi-account-group" title="Teams" />
      </v-list>
    </v-navigation-drawer>

    <v-main style="min-height: 100vh;">
      <v-container>
        <router-view />
      </v-container>
    </v-main>
  </v-layout>
</template>

<script>
import api from '../../api';

export default {
  data: () => ({
    drawer: true,
    loading: false,
  }),
  methods: {
    logout() {
      this.loading = true;
      api
        .post('/logout')
        .catch(() => {})
        .finally(() => {
          localStorage.removeItem('access_token');
          this.loading = false;
          this.$router.push({ name: 'admin.login' });
        });
    },
  },
};
</script>

