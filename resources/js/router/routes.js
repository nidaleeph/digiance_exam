import { computed } from "vue";
import { createRouter, createWebHistory } from "vue-router";
import Login from "../views/Login.vue";
import Register from "../views/Register.vue";
import Plans from "../views/Plans.vue";
import Dashboard from "../views/Dashboard.vue";
import ManageSubscription from "../views/ManageSubscription.vue";
import store from "@store";

const token = computed(() => store.state.user.token);

const routes = [
    { path: "/", redirect: "/plans" },
    { path: "/plans", name: "plans", component: Plans },
    { path: "/login", name: "login", component: Login },
    { path: "/register", name: "register", component: Register },
    {
        path: "/dashboard",
        name: "dashboard",
        component: Dashboard,
        beforeEnter: (to, from, next) => {
            if (!token) return next("/login");
            next();
        },
    },
    {
        path: "/manage-subscription",
        name: "manage-subscription",
        component: ManageSubscription,
        beforeEnter: (to, from, next) => {
            if (!token) return next("/login");
            next();
        },
    },
    { path: "/:pathMatch(.*)*", redirect: "/plans" },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
