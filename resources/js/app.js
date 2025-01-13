import { createApp } from "vue";
import App from "./App.vue";
import router from "./router/routes";
import "../css/app.css"; // Tailwind

import axios from "axios";
axios.defaults.withCredentials = true;
axios.defaults.baseURL = "/api"; // so calls go to /api/...

createApp(App).use(router).mount("#app");
