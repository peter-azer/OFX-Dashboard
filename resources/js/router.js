import { createRouter, createWebHistory } from 'vue-router';
import Login from './components/Admin/Login.vue';
import Dashboard from './components/Admin/Dashboard.vue';

const routes = [
    {
        path: '/admin/login',
        name: 'admin.login',
        component: Login,
    },
    {
        path: '/admin/dashboard',
        name: 'admin.dashboard',
        component: Dashboard,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
