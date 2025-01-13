<template>
    <div
        class="max-w-lg mx-auto mt-10 bg-gradient-to-r from-indigo-100 to-indigo-50 p-8 rounded-xl shadow-lg"
    >
        <h1 class="text-3xl font-bold text-indigo-600 mb-6 text-center">
            Register
        </h1>
        <form @submit.prevent="handleRegister" class="space-y-6">
            <div>
                <label class="block text-lg font-medium text-gray-700 mb-1">
                    Full Name
                </label>
                <input
                    v-model="user.fullName"
                    type="text"
                    class="w-full border rounded-md p-3 text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                    placeholder="Enter your full name"
                    autocomplete="off"
                    required
                />
            </div>

            <div>
                <label class="block text-lg font-medium text-gray-700 mb-1">
                    Email
                </label>
                <input
                    v-model="user.email"
                    type="email"
                    class="w-full border rounded-md p-3 text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                    placeholder="Enter your email"
                    autocomplete="off"
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
                        autocomplete="off"
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

            <div>
                <label class="block text-lg font-medium text-gray-700 mb-1">
                    Confirm Password
                </label>
                <div class="relative">
                    <input
                        v-model="confirmPassword"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        class="w-full border rounded-md p-3 text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                        placeholder="Re-enter your password"
                        autocomplete="off"
                        required
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-500"
                        @click="toggleConfirmPassword"
                    >
                        <span v-if="showConfirmPassword">🙈</span>
                        <span v-else>👁️</span>
                    </button>
                </div>
                <p v-if="passwordMismatch" class="text-red-500 text-sm mt-1">
                    Passwords do not match.
                </p>
            </div>

            <button
                :disabled="passwordMismatch"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 rounded-md transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Register
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import store from "@store";

const user = ref({
    fullName: "",
    email: "",
    password: "",
});

const confirmPassword = ref("");
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const errorMsg = ref("");
const router = useRouter();

const passwordMismatch = computed(
    () => user.value.password !== confirmPassword.value
);

function togglePassword() {
    showPassword.value = !showPassword.value;
}

function toggleConfirmPassword() {
    showConfirmPassword.value = !showConfirmPassword.value;
}

async function handleRegister() {
    if (passwordMismatch.value) {
        return;
    }

    try {
        await store.dispatch("register", user.value);
        router.push("/dashboard");
    } catch (error) {
        errorMsg.value = error.response?.data?.message || "Registration failed";
    }
}
</script>
