<template>
    <div class="flex items-center justify-center min-h-screen">
        <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-lg">
            <h1 class="text-3xl font-bold text-indigo-600 mb-6 text-center">
                Login
            </h1>

            <transition name="fade">
                <div
                    v-if="errorMsg"
                    class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                >
                    <strong class="font-bold">Error: </strong>
                    <span class="block sm:inline">{{ errorMsg }}</span>
                </div>
            </transition>

            <form @submit.prevent="handleLogin" class="space-y-6">
                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Email
                    </label>
                    <input
                        v-model="user.email"
                        type="email"
                        class="w-full border rounded-md p-3 text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                        placeholder="Enter your email"
                        required
                    />
                </div>

                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Password
                    </label>
                    <div class="relative">
                        <input
                            v-model="user.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="w-full border rounded-md p-3 text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            placeholder="Enter your password"
                            required
                        />
                        <button
                            type="button"
                            class="absolute inset-y-0 right-3 flex items-center text-gray-500"
                            @click="togglePassword"
                        >
                            <span v-if="showPassword">🙈</span>
                            <span v-else>👁️</span>
                        </button>
                    </div>
                </div>

                <button
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 rounded-md transition-all duration-300"
                >
                    Login
                </button>

                <p class="text-center text-gray-600 mt-4">
                    Don’t have an account?
                    <router-link
                        to="/register"
                        class="text-indigo-600 hover:underline"
                    >
                        Register here
                    </router-link>
                </p>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import store from "@store";

const router = useRouter();

const user = ref({
    email: "",
    password: "",
});

const errorMsg = ref(null);
const showPassword = ref(false);

function togglePassword() {
    showPassword.value = !showPassword.value;
}

async function handleLogin() {
    try {
        errorMsg.value = null;
        await store.dispatch("login", user.value);
        router.push("/dashboard");
    } catch (error) {
        const response = error?.response;
        errorMsg.value =
            response?.data?.message ||
            response?.data?.error ||
            "Login failed. Please try again.";
    }
}
</script>
