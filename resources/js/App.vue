<template>
    <div>
        <LoadingSpinner v-if="loading" :message="message" />
        <Navbar />
        <div class="container mx-auto">
            <router-view />
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import Navbar from "@components/Navbar.vue";
import axios from "axios";

import store from "@store";

import LoadingSpinner from "./components/Loader.vue";

const loading = computed(() => store.state.loading.isLoading);
const message = computed(() => store.state.loading.message);
const router = useRouter();
const errorMsg = ref(null);
</script>

<style scoped></style>
