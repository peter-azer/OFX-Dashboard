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
        <v-list-item v-if="hasPermission('view analytics')" :to="{ name: 'admin.analytics' }" title="Analytics">
          <template #prepend>
            <Squares2X2Icon class="h-5 w-5 mx-4" />
          </template>
        </v-list-item>
        <v-list-item v-if="hasPermission('view heroes')" :to="{ name: 'admin.heroes' }" title="Heroes">
          <template #prepend>
            <StarIcon class="h-5 w-5 mx-4" />
          </template>
        </v-list-item>
        <v-list-item v-if="hasPermission('view brands')" :to="{ name: 'admin.brands' }" title="Brands">
          <template #prepend>
            <TagIcon class="h-5 w-5 mx-4" />
          </template>
        </v-list-item>
        <v-list-item v-if="hasPermission('view abouts')" :to="{ name: 'admin.abouts' }" title="Abouts">
          <template #prepend>
            <InformationCircleIcon class="h-5 w-5 mx-4" />
          </template>
        </v-list-item>
        <v-list-item v-if="hasPermission('view services')" :to="{ name: 'admin.services' }" title="Services">
          <template #prepend>
            <BriefcaseIcon class="h-5 w-5 mx-4" />
          </template>
        </v-list-item>
        <v-list-item v-if="hasPermission('view works')" :to="{ name: 'admin.works' }" title="Works">
          <template #prepend>
            <BriefcaseIcon class="h-5 w-5 mx-4" />
          </template>
        </v-list-item>
        <v-list-item v-if="hasPermission('view teams')" :to="{ name: 'admin.teams' }" title="Teams">
          <template #prepend>
            <UsersIcon class="h-5 w-5 mx-4" />
          </template>
        </v-list-item>
        <v-list-item v-if="hasPermission('view users')" :to="{ name: 'admin.users' }" title="Users">
          <template #prepend>
            <UsersIcon class="h-5 w-5 mx-4" />
          </template>
        </v-list-item>
        <v-list-item v-if="hasPermission('view phone')" :to="{ name: 'admin.phone-contacts' }" title="Phone Contacts">
          <template #prepend>
            <PhoneIcon class="h-5 w-5 mx-4" />
          </template>
        </v-list-item>
        <v-list-item v-if="hasPermission('view whatsapp')" :to="{ name: 'admin.whatsapp-contacts' }" title="WhatsApp Contacts">
          <template #prepend>
            <ChatBubbleOvalLeftIcon class="h-5 w-5 mx-4" />
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
import {
  ArrowRightOnRectangleIcon,
  Squares2X2Icon,
  StarIcon,
  TagIcon,
  InformationCircleIcon,
  BriefcaseIcon,
  UsersIcon,
  PhoneIcon,
  ChatBubbleOvalLeftIcon,
} from '@heroicons/vue/24/outline';

export default {
  components: {
    ArrowRightOnRectangleIcon,
    Squares2X2Icon,
    StarIcon,
    TagIcon,
    InformationCircleIcon,
    BriefcaseIcon,
    UsersIcon,
    PhoneIcon,
    ChatBubbleOvalLeftIcon,
  },

  data() {
    return {
      drawer: true,
      loading: false,
      permissions: {}, // dynamically loaded permissions
    };
  },
  mounted() {
    this.loadUserPermissions();
    console.log(this.loadUserPermissions());
  },

  methods: {
    /**
     * Load user permissions from API or localStorage
     */
    async loadUserPermissions() {
      try {
        // First check if permissions are cached
        let userPermissions = JSON.parse(localStorage.getItem('permissions'));

        if (!userPermissions || userPermissions.length === 0) {
          // Fetch from API if not cached
          const res = await api.get('/user'); // Adjust endpoint if needed
          userPermissions = res.data.all_permissions || [];

          // Save to localStorage for faster access
          localStorage.setItem('permissions', JSON.stringify(userPermissions));
        }

        // Update local data
        this.permissions = userPermissions.reduce((acc, perm) => {
          acc[perm.replace(/\s+/g, '_')] = true;
          return acc;
        }, {});
      } catch (error) {
        console.error('Failed to load user permissions:', error);
      }
    },

    /**
     * Check if the current user has a specific permission
     */
    hasPermission(permission) {
      const userPermissions =
        JSON.parse(localStorage.getItem('permissions') || '[]');
      return userPermissions.includes(permission);
    },

    /**
     * Log the user out
     */
    logout() {
      this.loading = true;
      api
        .post('/logout')
        .catch(() => {})
        .finally(() => {
          localStorage.removeItem('access_token');
          localStorage.removeItem('permissions');
          this.loading = false;
          this.$router.push({ name: 'admin.login' });
        });
    },
  },
};
</script>


