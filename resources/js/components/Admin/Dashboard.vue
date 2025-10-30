<template>
  <v-layout>
    <v-app-bar color="primary" prominent>
      <v-app-bar-nav-icon variant="text" @click.stop="drawer = !drawer" />
      <v-toolbar-title>Admin Panel</v-toolbar-title>
      <v-spacer />
      <v-btn @click="logout" :loading="loading" variant="text" icon>
        <v-icon size="20">
          <ArrowRightOnRectangleIcon />
        </v-icon>
      </v-btn>
    </v-app-bar>

    <v-navigation-drawer v-model="drawer" permanent>
      <v-list-item title="OFX Dashboard" subtitle="Content Manager" />
      <v-divider />
      <v-list density="compact" nav>
        <v-list-item :to="{ name: 'admin.analytics' }" title="Analytics">
          <template #prepend>
            <Squares2X2Icon class="h-5 w-5" />
          </template>
        </v-list-item>
        <v-list-item :to="{ name: 'admin.heroes' }" title="Heroes">
          <template #prepend>
            <StarIcon class="h-5 w-5" />
          </template>
        </v-list-item>
        <v-list-item :to="{ name: 'admin.brands' }" title="Brands">
          <template #prepend>
            <TagIcon class="h-5 w-5" />
          </template>
        </v-list-item>
        <v-list-item :to="{ name: 'admin.abouts' }" title="Abouts">
          <template #prepend>
            <InformationCircleIcon class="h-5 w-5" />
          </template>
        </v-list-item>
        <v-list-item :to="{ name: 'admin.services' }" title="Services">
          <template #prepend>
            <BriefcaseIcon class="h-5 w-5" />
          </template>
        </v-list-item>
        <v-list-item :to="{ name: 'admin.works' }" title="Works">
          <template #prepend>
            <BriefcaseIcon class="h-5 w-5" />
          </template>
        </v-list-item>
        <v-list-item :to="{ name: 'admin.teams' }" title="Teams">
          <template #prepend>
            <UsersIcon class="h-5 w-5" />
          </template>
        </v-list-item>
        <v-list-item :to="{ name: 'admin.phone-contacts' }" title="Phone Contacts">
          <template #prepend>
            <PhoneIcon class="h-5 w-5" />
          </template>
        </v-list-item>
        <v-list-item :to="{ name: 'admin.whatsapp-contacts' }" title="WhatsApp Contacts">
          <template #prepend>
            <ChatBubbleOvalLeftIcon class="h-5 w-5" />
          </template>
        </v-list-item>
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
import { ArrowRightOnRectangleIcon, Squares2X2Icon, StarIcon, TagIcon, InformationCircleIcon, BriefcaseIcon, UsersIcon, PhoneIcon, ChatBubbleOvalLeftIcon } from '@heroicons/vue/24/outline';

export default {
  components: { ArrowRightOnRectangleIcon, Squares2X2Icon, StarIcon, TagIcon, InformationCircleIcon, BriefcaseIcon, UsersIcon, PhoneIcon, ChatBubbleOvalLeftIcon },
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

