import { createRouter, createWebHistory } from 'vue-router';
import Login from '../components/Admin/Login.vue';
import Dashboard from '../components/Admin/Dashboard.vue';
import Users from '../components/Admin/Users/Index.vue';

const routes = [
    {
        path: '/admin/login',
        name: 'Login',
        component: Login,
    },
    {
        path: '/admin',
        component: Dashboard,
        children: [
            {
                path: '',
                name: 'Dashboard',
                component: () => import('../components/Admin/Home.vue'),
            },
            {
                path: 'users',
                name: 'Users',
                component: Users,
                meta: { requiresAuth: true, permission: 'view users' }
            },
        ]
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
