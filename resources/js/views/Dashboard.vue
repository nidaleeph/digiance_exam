<template>
    <div
        class="container mx-auto py-10 px-4 md:px-10 bg-gradient-to-b from-gray-100 to-gray-200 min-h-screen"
    >
        <h1 class="text-4xl font-bold text-indigo-600 mb-8 text-center">
            Dashboard
        </h1>

        <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-8">
            <h2
                class="text-lg font-semibold text-white bg-indigo-500 px-6 py-4"
            >
                User Information
            </h2>
            <table class="w-full table-auto">
                <tbody>
                    <tr class="border-t">
                        <td class="px-6 py-4 font-medium text-gray-700">
                            Name
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ user.name }}</td>
                    </tr>
                    <tr class="border-t">
                        <td class="px-6 py-4 font-medium text-gray-700">
                            Email
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ user.email }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-8">
            <h2
                class="text-lg font-semibold text-white bg-indigo-500 px-6 py-4"
            >
                Subscription Details
            </h2>
            <table class="w-full table-auto">
                <tbody>
                    <tr class="border-t">
                        <td class="px-6 py-4 font-medium text-gray-700">
                            Current Plan
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ subscription?.plan?.name || "None" }}
                        </td>
                    </tr>
                    <tr class="border-t">
                        <td class="px-6 py-4 font-medium text-gray-700">
                            Next Billing Date
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{
                                subscriptionStatus === "active"
                                    ? unixFormatDate(stripeInvoice?.period_end)
                                    : "N/A"
                            }}
                        </td>
                    </tr>
                    <tr class="border-t">
                        <td class="px-6 py-4 font-medium text-gray-700">
                            Amount Due
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{
                                subscriptionStatus === "active"
                                    ? formatCurrency(
                                          stripeInvoice?.amount_due / 100,
                                          stripeInvoice?.currency
                                      )
                                    : "N/A"
                            }}
                        </td>
                    </tr>
                    <tr class="border-t">
                        <td class="px-6 py-4 font-medium text-gray-700">
                            Credit Remaining
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{
                                subscriptionStatus === "active"
                                    ? formatCurrency(
                                          Math.abs(
                                              stripeInvoice?.starting_balance /
                                                  100
                                          ),
                                          stripeInvoice?.currency
                                      )
                                    : "N/A"
                            }}
                        </td>
                    </tr>
                    <tr class="border-t">
                        <td class="px-6 py-4 font-medium text-gray-700">
                            Status
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ subscription?.status }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <h2
                class="text-lg font-semibold text-white bg-indigo-500 px-6 py-4"
            >
                Payment History
            </h2>
            <template v-if="payments.length > 0">
                <table class="w-full table-auto border-collapse">
                    <thead class="bg-indigo-50">
                        <tr>
                            <th
                                class="px-4 py-2 text-left font-medium text-indigo-800 border-b"
                            >
                                Plan
                            </th>
                            <th
                                class="px-4 py-2 text-left font-medium text-indigo-800 border-b"
                            >
                                Amount
                            </th>
                            <th
                                class="px-4 py-2 text-left font-medium text-indigo-800 border-b"
                            >
                                Status
                            </th>
                            <th
                                class="px-4 py-2 text-left font-medium text-indigo-800 border-b"
                            >
                                Date
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="payment in payments"
                            :key="payment.id"
                            class="hover:bg-teal-50 transition-colors"
                        >
                            <td class="px-4 py-2 text-gray-700">
                                {{ payment.plan.name }}
                            </td>
                            <td class="px-4 py-2 text-gray-700">
                                {{ formatCurrency(payment.amount, "USD") }}
                            </td>
                            <td
                                class="px-4 py-2 capitalize text-gray-700 font-medium"
                            >
                                {{ payment.status }}
                            </td>
                            <td class="px-4 py-2 text-gray-700">
                                {{ formatDate(payment.created_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </template>
            <p v-else class="text-red-500 px-6 py-4">
                <strong>No payment history found.</strong>
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import moment from "moment";
import store from "@store";
import axiosClient from "@resources/axios";

const user = ref({});
const subscription = ref(null);
const subscriptionStatus = ref(null);
const stripeInvoice = ref(null);
const payments = ref([]);
const isLoading = ref(true);

onMounted(async () => {
    try {
        const { data } = await store.dispatch("getUser");
        user.value = data;
        await fetchSubscription();
        await fetchPayments();
    } catch (err) {
        console.error("Error fetching dashboard:", err);
    } finally {
        isLoading.value = false;
    }
});

async function fetchSubscription() {
    try {
        const { data } = await axiosClient.get("/subscription");
        subscription.value = data.subscription;
        stripeInvoice.value = data.stripeInvoice;
        subscriptionStatus.value = data.subscription.status;
    } catch (err) {
        console.error("Failed to fetch subscription:", err);
    }
}

async function fetchPayments() {
    try {
        const { data } = await axiosClient.get("/payments/history");
        payments.value = data;
    } catch (err) {
        console.error("Failed to fetch payment history:", err);
    }
}

function unixFormatDate(date) {
    if (!date) return "N/A";
    return moment.unix(date).format("MMM D, YYYY | h:mm A");
}

function formatDate(date) {
    if (!date) return "N/A";
    return moment(date).format("MMM D, YYYY | h:mm A");
}

const formatCurrency = (amount, currency = "USD", locale = "en-US") => {
    return new Intl.NumberFormat(locale, {
        style: "currency",
        currency,
    }).format(amount);
};
</script>
