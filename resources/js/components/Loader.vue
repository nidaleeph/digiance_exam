<template>
    <div
        class="fixed inset-0 flex justify-center items-center bg-black bg-opacity-60 z-[10000] flex-col p-6"
    >
        <div
            class="spinner-border animate-spin border-t-4 border-transparent border-solid rounded-full w-32 h-32 mb-6 shadow-2xl"
        ></div>

        <div
            class="text-white text-2xl text-center font-semibold animate-fade-up animate-once animate-duration-[2000ms]"
        >
            {{
                message
                    ? message + "..."
                    : "Please wait while we refresh the data..."
            }}
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    message: String,
});
</script>

<style scoped>
.spinner-border {
    border-width: 5px;
    border-color: transparent;
    border-top-color: rgb(79 70 229);
    border-radius: 50%;
    animation-duration: 1.5s;
}
</style>
