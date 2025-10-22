<template>
    <v-layout>
        <v-app-bar color="primary" prominent>
            <v-app-bar-nav-icon variant="text" @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
            <v-toolbar-title>Admin Panel</v-toolbar-title>
        </v-app-bar>

        <v-navigation-drawer v-model="drawer" temporary>
            <v-list-item
                prepend-avatar="https://randomuser.me/api/portraits/men/78.jpg"
                title="John Leider"
            ></v-list-item>

            <v-divider></v-divider>

            <v-list density="compact" nav>
                <v-list-item prepend-icon="mdi-view-dashboard" title="Dashboard" value="dashboard"></v-list-item>
                <v-list-item prepend-icon="mdi-forum" title="About" value="about"></v-list-item>
            </v-list>
        </v-navigation-drawer>

        <v-main style="height: 100vh;">
            <v-container>
                <h1>Dashboard</h1>
            </v-container>
        </v-main>
    </v-layout>
</template>

<script>
export default {
    data: () => ({
        drawer: false,
    }),
};
</script>
