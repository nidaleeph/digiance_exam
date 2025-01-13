import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import path from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        vue(),
    ],
    resolve: {
        alias: {
            "@components": path.resolve(__dirname, "resources/js/components"),
            "@store": path.resolve(__dirname, "resources/js/store"),
            "@views": path.resolve(__dirname, "resources/js/views"),
            "@resources": path.resolve(__dirname, "resources/js"),
            "@routes": path.resolve(__dirname, "resources/js/router/routes.js"),
        },
    },
});
