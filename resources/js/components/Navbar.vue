<template>
    <div>
        <nav class="bg-indigo-700 text-white shadow-md">
            <div
                class="container mx-auto flex justify-between items-center py-4 px-6"
            >
                <div>
                    <router-link
                        to="/"
                        class="text-2xl font-bold hover:text-indigo-300 transition-colors"
                    >
                        Digiance
                    </router-link>
                </div>

                <div class="md:hidden">
                    <button
                        @click="isMobileMenuOpen = !isMobileMenuOpen"
                        class="focus:outline-none"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            class="w-6 h-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>
                </div>

                <div class="hidden md:flex space-x-6">
                    <router-link
                        to="/dashboard"
                        class="nav-link"
                        :class="{ active: $route.path === '/dashboard' }"
                        v-if="token"
                    >
                        Dashboard
                    </router-link>
                    <router-link
                        to="/manage-subscription"
                        class="nav-link"
                        :class="{
                            active: $route.path === '/manage-subscription',
                        }"
                        v-if="token"
                    >
                        Manage Subscription
                    </router-link>
                    <router-link
                        to="/plans"
                        class="nav-link"
                        :class="{ active: $route.path === '/plans' }"
                    >
                        Plans
                    </router-link>
                </div>

                <div class="hidden md:flex items-center space-x-4">
                    <button v-if="!token" class="auth-button" @click="goLogin">
                        Login
                    </button>
                    <button
                        v-if="!token"
                        class="auth-button"
                        @click="goRegister"
                    >
                        Register
                    </button>
                    <button v-if="token" class="logout-button" @click="logout">
                        Logout
                    </button>
                </div>
            </div>

            <div
                v-if="isMobileMenuOpen"
                class="md:hidden bg-indigo-800 space-y-2 p-4"
            >
                <router-link
                    to="/dashboard"
                    class="block nav-link text-center"
                    :class="{ active: $route.path === '/dashboard' }"
                    v-if="token"
                >
                    Dashboard
                </router-link>
                <router-link
                    to="/manage-subscription"
                    class="block nav-link text-center"
                    :class="{ active: $route.path === '/manage-subscription' }"
                    v-if="token"
                >
                    Manage Subscription
                </router-link>
                <router-link
                    to="/plans"
                    class="block nav-link text-center"
                    :class="{ active: $route.path === '/plans' }"
                >
                    Plans
                </router-link>
                <button
                    v-if="!token"
                    class="auth-button block w-full text-center"
                    @click="goLogin"
                >
                    Login
                </button>
                <button
                    v-if="!token"
                    class="auth-button block w-full text-center"
                    @click="goRegister"
                >
                    Register
                </button>
                <button
                    v-if="token"
                    class="logout-button block w-full text-center"
                    @click="logout"
                >
                    Logout
                </button>
            </div>
        </nav>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import store from "@store";

const router = useRouter();
const isMobileMenuOpen = ref(false);

const token = computed(() => store.state.user.token);

function goLogin() {
    isMobileMenuOpen.value = false;
    router.push("/login");
}

function goRegister() {
    isMobileMenuOpen.value = false;
    router.push("/register");
}

async function logout() {
    isMobileMenuOpen.value = false;
    try {
        await store.dispatch("logout");
        router.push("/plans");
    } catch (err) {
        console.error("Logout failed", err);
    }
}
</script>
<style scoped>
.nav-link {
    padding: 0.5rem 1rem;
    font-size: 1rem;
    font-weight: 500;
    color: white;
    border-radius: 0.375rem;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.nav-link:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.nav-link.active {
    background-color: rgba(255, 255, 255, 0.3);
    font-weight: 600;
}

.auth-button {
    padding: 0.5rem 1rem;
    font-size: 1rem;
    font-weight: 500;
    color: white;
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 0.375rem;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.auth-button:hover {
    background-color: rgba(255, 255, 255, 0.3);
    color: black;
}

.logout-button {
    padding: 0.5rem 1rem;
    font-size: 1rem;
    font-weight: 500;
    color: white;
    background-color: #e11d48; /* Custom red */
    border-radius: 0.375rem;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.logout-button:hover {
    background-color: #be123c; /* Darker red for hover */
    color: white;
}

@media (max-width: 768px) {
    .nav-link,
    .auth-button,
    .logout-button {
        display: block;
        text-align: center;
        margin: 0.5rem 0;
    }
}
</style>
