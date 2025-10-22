import { createRouter, createWebHistory } from 'vue-router';
import Login from '../components/Admin/Login.vue';
import Dashboard from '../components/Admin/Dashboard.vue';

const routes = [
    {
        path: '/admin/login',
        name: 'Login',
        component: Login,
    },
    {
        path: '/admin',
        name: 'Dashboard',
        component: Dashboard,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
