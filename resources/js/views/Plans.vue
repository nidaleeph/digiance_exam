<template>
    <div class="bg-gray-100 min-h-screen">
        <div class="container mx-auto pt-10">
            <h1 class="text-3xl font-bold text-indigo-700 mb-6 mx-4">
                Available Plans
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-4">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="bg-white shadow-md rounded-lg p-6 flex flex-col items-center"
                >
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">
                        {{ plan.name }}
                    </h2>
                    <p class="text-gray-600 text-center mb-4">
                        {{ plan.description }}
                    </p>
                    <p class="text-lg font-bold text-indigo-700 mb-6">
                        ${{ plan.price }} / {{ plan.billing_interval }}
                    </p>
                    <button
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700"
                        @click="initiatePayment(plan.id)"
                    >
                        Subscribe
                    </button>
                </div>
            </div>
        </div>

        <transition name="fade">
            <div
                v-if="showPaymentSection"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            >
                <div
                    class="bg-white rounded-lg shadow-lg w-full max-w-xl p-6 relative"
                >
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">
                        Enter Your Payment Information
                    </h2>

                    <div class="mb-6">
                        <p class="text-lg font-semibold text-gray-700">
                            {{ selectedPlan?.name || "Selected Plan" }}
                        </p>
                        <p class="text-gray-600">
                            {{ selectedPlan?.description || "" }}
                        </p>
                        <p class="text-lg font-bold text-indigo-700">
                            ${{ selectedPlan?.price || "" }} /
                            {{ selectedPlan?.billing_interval || "" }}
                        </p>
                    </div>

                    <div class="mb-4">
                        <label
                            for="cardholder-name"
                            class="block text-gray-700 font-medium mb-2"
                        >
                            Cardholder Name (optional)
                        </label>
                        <input
                            id="cardholder-name"
                            v-model="cardholderName"
                            type="text"
                            class="border border-gray-300 rounded-md p-2 w-full"
                            placeholder="Your Name"
                        />
                    </div>
                    <label
                        class="block text-gray-700 font-medium mb-2"
                        for="card-element"
                    >
                        Card Details
                    </label>
                    <div
                        id="card-element"
                        class="border border-gray-300 rounded-md p-2 mb-6"
                    ></div>

                    <transition name="fade">
                        <div
                            v-if="notification.message"
                            :class="{
                                'bg-red-100 border-red-500 text-red-700':
                                    notification.isError,
                                'bg-green-100 border-green-500 text-green-700':
                                    !notification.isError,
                            }"
                            class="border px-4 py-3 rounded mb-6"
                        >
                            {{ notification.message }}
                        </div>
                    </transition>
                    <div
                        v-if="subscription"
                        class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md mb-4"
                    >
                        <p class="font-medium">Notice:</p>
                        <p class="text-sm">
                            You already have an active subscription. Proceeding
                            will update your current subscription and set this
                            card as your default payment method. Please confirm
                            before proceeding.
                        </p>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button
                            :disabled="isProcessing"
                            :class="{
                                'bg-gray-400 cursor-not-allowed': isProcessing,
                                'bg-gray-300 hover:bg-gray-400': !isProcessing,
                            }"
                            class="px-6 py-2 rounded-lg text-gray-700"
                            @click="closePaymentModal"
                        >
                            Cancel
                        </button>
                        <button
                            :disabled="isProcessing"
                            :class="{
                                'bg-gray-400 cursor-not-allowed': isProcessing,
                                'bg-green-600 hover:bg-green-700':
                                    !isProcessing,
                            }"
                            class="px-6 py-2 rounded-lg text-white"
                            @click="confirmCardPayment"
                        >
                            Confirm Payment
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from "vue";
import axiosClient from "@resources/axios";
import { loadStripe } from "@stripe/stripe-js";
import store from "@store";
import { useRouter } from "vue-router";

const router = useRouter();

const token = computed(() => store.state.user.token);

const plans = ref([]);
const showPaymentSection = ref(false);
const cardholderName = ref("");
const selectedPlan = ref(null);

const subscription = ref({});

let stripe = null;
let cardElement = null;

const clientSecret = ref(null);

const notification = ref({
    message: "",
    isError: false,
});

const isProcessing = ref(false);

function showNotification(message, isError = false) {
    notification.value = { message, isError };
    setTimeout(() => (notification.value.message = ""), 5000);
}

onMounted(async () => {
    try {
        if (token.value) {
            const user = await store.dispatch("getUser");
            subscription.value = user.subscription ?? null;
        }
        const { data } = await axiosClient.get("/plans");
        plans.value = data;
    } catch (err) {
        showNotification("Failed to load plans. Please try again later.", true);
        console.error("Failed to load plans:", err.message);
    }

    stripe = await loadStripe(import.meta.env.VITE_STRIPE_PUBLIC_KEY);
    if (!stripe) {
        showNotification(
            "Could not initialize Stripe. Please try again later.",
            true
        );
        console.error("Could not initialize Stripe.");
    }
});

async function initiatePayment(planId) {
    if (!token.value) {
        router.push("/login");
        return;
    }

    selectedPlan.value = plans.value.find((plan) => plan.id === planId);
    showPaymentSection.value = true;

    await nextTick();

    if (!cardElement && stripe) {
        const elements = stripe.elements();
        const style = {
            base: {
                color: "#32325d",
                fontFamily: "Arial, sans-serif",
                fontSize: "16px",
                "::placeholder": { color: "#888" },
            },
            invalid: { color: "#fa755a" },
        };

        cardElement = elements.create("card", { style });
        cardElement.mount("#card-element");
    }
}

async function confirmCardPayment() {
    if (!cardElement || !stripe) {
        showNotification("Payment form not initialized properly.", true);
        return;
    }

    isProcessing.value = true;

    try {
        const { paymentMethod, error } = await stripe.createPaymentMethod({
            type: "card",
            card: cardElement,
            billing_details: {
                name: cardholderName.value || undefined,
            },
        });

        if (error) {
            showNotification(error.message, true);
            isProcessing.value = false;
            return;
        }

        const { data } = await axiosClient.post("/create-payment-details", {
            payment_method_id: paymentMethod.id,
        });

        await finalizeSubscription(selectedPlan.value.id);
    } catch (err) {
        showNotification(err.response?.data?.error || "Payment failed", true);
        isProcessing.value = false;
    }
}

async function finalizeSubscription(planId) {
    try {
        const { data } = await axiosClient.post("/subscribe", {
            plan_id: planId,
        });
        showNotification(data.message || "Subscription successful!");
        closePaymentModal();
        router.push("/dashboard");
    } catch (err) {
        showNotification(
            err.response?.data?.error || "Subscription finalization failed",
            true
        );
    } finally {
        isProcessing.value = false;
    }
}

const closePaymentModal = () => {
    if (cardElement) {
        cardElement.unmount();
        cardElement = null;
    }
    selectedPlan.value = null;
    cardholderName.value = "";
    showPaymentSection.value = false;
    isProcessing.value = false;
};
</script>
