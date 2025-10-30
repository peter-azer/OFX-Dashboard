import { createRouter, createWebHistory } from "vue-router";
import Login from "./components/Admin/Login.vue";
import Dashboard from "./components/Admin/Dashboard.vue";
import Heroes from "./components/Admin/Heroes.vue";
import Brands from "./components/Admin/Brands.vue";
import Abouts from "./components/Admin/Abouts.vue";
import Services from "./components/Admin/Services.vue";
import Works from "./components/Admin/Works.vue";
import Teams from "./components/Admin/Teams.vue";
import PhoneContacts from "./components/Admin/PhoneContacts.vue";
import WhatsAppContacts from "./components/Admin/WhatsAppContacts.vue";
import Analysis from "./components/Admin/Analysis.vue";

const routes = [
    {
        path: "/admin/login",
        name: "admin.login",
        component: Login,
        meta: { guestOnly: true },
    },
    {
        path: "/admin",
        component: Dashboard,
        meta: { requiresAuth: true },
        children: [
            {
                path: "",
                redirect: { name: "admin.analytics" },
            },
            {
                path: "dashboard",
                name: "admin.dashboard",
                component: { template: "<div><h1>Dashboard</h1></div>" },
                meta: { requiresAuth: true },
            },
            {
                path: "analytics",
                name: "admin.analytics",
                component: Analysis,
                meta: { requiresAuth: true },
            },
            {
                path: "heroes",
                name: "admin.heroes",
                component: Heroes,
                meta: { requiresAuth: true },
            },
            {
                path: "brands",
                name: "admin.brands",
                component: Brands,
                meta: { requiresAuth: true },
            },
            {
                path: "abouts",
                name: "admin.abouts",
                component: Abouts,
                meta: { requiresAuth: true },
            },
            {
                path: "services",
                name: "admin.services",
                component: Services,
                meta: { requiresAuth: true },
            },
            {
                path: "works",
                name: "admin.works",
                component: Works,
                meta: { requiresAuth: true },
            },
            {
                path: "teams",
                name: "admin.teams",
                component: Teams,
                meta: { requiresAuth: true },
            },
            {
                path: "phone-contacts",
                name: "admin.phone-contacts",
                component: PhoneContacts,
                meta: { requiresAuth: true },
            },
            {
                path: "whatsapp-contacts",
                name: "admin.whatsapp-contacts",
                component: WhatsAppContacts,
                meta: { requiresAuth: true },
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem("access_token");
    if (to.meta.requiresAuth && !token) {
        next({ name: "admin.login" });
    } else if (to.meta.guestOnly && token) {
        next({ name: "admin.dashboard" });
    } else {
        next();
    }
});

export default router;
