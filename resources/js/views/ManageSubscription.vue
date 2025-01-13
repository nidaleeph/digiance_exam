<template>
    <div class="container mx-auto py-10 px-4 md:px-10" v-if="!isLoading">
        <h1 class="text-3xl font-bold text-indigo-700 mb-6">
            Manage Subscription
        </h1>

        <!-- Current Subscription -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                Current Subscription
            </h2>
            <div v-if="subscriptionStatus === 'active'">
                <p class="text-gray-700">
                    <strong>Current Plan:</strong> {{ subscription.plan.name }}
                </p>
                <!-- <button
                    class="mt-4 bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700"
                    @click="showCancelModal = true"
                >
                    Cancel Subscription
                </button> -->
            </div>
            <div v-else>
                <p class="text-red-500">
                    <strong>No subscription found.</strong>
                </p>
            </div>
        </div>

        <!-- Select a New Plan -->
        <div
            class="bg-white shadow-md rounded-lg p-6 mb-6"
            v-if="subscriptionStatus === 'active'"
        >
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                Select a New Plan
            </h2>
            <div>
                <label class="block font-medium mb-2 text-gray-700"
                    >Available Plans</label
                >
                <select
                    v-model="selectedPlanId"
                    class="border-black rounded-md p-2 mr-4 border-2"
                >
                    <option
                        v-for="plan in plans"
                        :key="plan.id"
                        :value="plan.id"
                    >
                        {{ plan.name }} - ${{ plan.price }}/{{
                            plan.billing_interval
                        }}
                    </option>
                </select>
                <button
                    class="mt-4 bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700"
                    @click="showUpdateModal = true"
                >
                    Update Plan
                </button>
            </div>
        </div>

        <!-- Confirm Update Modal -->
        <transition name="fade">
            <div
                v-if="showUpdateModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            >
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        Confirm Plan Change
                    </h2>
                    <p class="mb-4 text-gray-700">
                        You are about to change your subscription from:
                    </p>
                    <div class="mb-4">
                        <p class="text-gray-700">
                            <strong>Current Plan:</strong>
                            {{ initialPlan.name }}
                        </p>
                        <p class="text-gray-700">
                            <strong>Selected Plan:</strong>
                            {{ selectedPlan.name }}
                        </p>
                    </div>
                    <p class="text-gray-700 mb-6">
                        Are you sure you want to proceed?
                    </p>
                    <div class="flex justify-end space-x-4">
                        <button
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400"
                            @click="showUpdateModal = false"
                        >
                            Cancel
                        </button>
                        <button
                            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700"
                            @click="confirmUpdate"
                        >
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Confirm Cancel Modal -->
        <transition name="fade">
            <div
                v-if="showCancelModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            >
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        Confirm Cancellation
                    </h2>
                    <p class="mb-4 text-gray-700">
                        You are about to cancel your subscription.
                    </p>
                    <p class="text-gray-700 mb-6">
                        This action cannot be undone. Are you sure you want to
                        proceed?
                    </p>
                    <div class="flex justify-end space-x-4">
                        <button
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400"
                            @click="showCancelModal = false"
                        >
                            Cancel
                        </button>
                        <button
                            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700"
                            @click="confirmCancellation"
                        >
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import axiosClient from "@resources/axios";

const subscription = ref(null);
const plans = ref([]);
const selectedPlanId = ref(null);
const isLoading = ref(true);
const showUpdateModal = ref(false);
const showCancelModal = ref(false);
const subscriptionStatus = ref(null);

const router = useRouter();

const initialPlan = computed(() => {
    return subscription.value?.plan || { name: "None", price: 0 };
});

const selectedPlan = computed(() => {
    return (
        plans.value.find((plan) => plan.id === selectedPlanId.value) || {
            name: "None",
            price: 0,
        }
    );
});

onMounted(async () => {
    try {
        const subRes = await axiosClient.get("/subscription");
        subscription.value = subRes.data.subscription;
        subscriptionStatus.value = subRes.data.subscription.status;

        const planRes = await axiosClient.get("/plans");
        plans.value = planRes.data;

        if (subscription.value) {
            selectedPlanId.value = subscription.value.plan_id;
        }
    } catch (err) {
        console.error("Error fetching subscription or plans:", err);
    } finally {
        isLoading.value = false;
    }
});

async function confirmUpdate() {
    try {
        const { data } = await axiosClient.post("/subscription/update", {
            plan_id: selectedPlanId.value,
        });

        subscription.value = data.subscription;
        showUpdateModal.value = false;

        router.push("/dashboard");
    } catch (err) {
        console.error("Failed to update subscription:", err);
        showUpdateModal.value = false;
    }
}

async function confirmCancellation() {
    try {
        const { data } = await axiosClient.post("/subscription/cancel");

        subscription.value = null;
        subscriptionStatus.value = "canceled";
        showCancelModal.value = false;

        router.push("/dashboard");
    } catch (err) {
        console.error("Failed to cancel subscription:", err);
        showCancelModal.value = false;
    }
}
</script>
