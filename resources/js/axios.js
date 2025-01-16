import axios from "axios";
import store from "./store";
import router from "./router/routes.js";

const cancelTokenSources = new Map();

axios.defaults.withCredentials = true;
axios.defaults.baseURL = "/api";

const axiosClient = axios.create();

const endpointDescriptions = {
    "/login": "Logging into your account",
    "/logout": "Logging out of your account",
    "/plans": "Fetching all subscription plans",
    "/register": "Registering your information",
    "/subscription": "Fetching user subscription details",
    "/subscription/update": "Updating user subscription details",
    "/payments/subscribe": "Subscribing the user to a plan",
    "/payments/history": "Fetching payment history",
    "/create-payment-details": "Creating a payment details",
};

axiosClient.interceptors.request.use((config) => {
    const endpoint = config.url;

    const loadingMessage =
        endpointDescriptions[endpoint] || "Processing your request";

    store.dispatch("startLoading", loadingMessage);

    const source = axios.CancelToken.source();
    config.cancelToken = source.token;

    const requestKey = `${config.url}-${config.method}`;
    cancelTokenSources.set(requestKey, source);

    config.headers.Authorization = `Bearer ${store.state.user.token}`;
    return config;
});

axiosClient.interceptors.response.use(
    (response) => {
        const requestKey = `${response.config.url}-${response.config.method}`;
        cancelTokenSources.delete(requestKey);

        store.dispatch("stopLoading");

        return response;
    },
    (error) => {
        if (error.response && error.response.status === 401) {
            store.commit("setToken", null);
            router.push({ name: "login" });
        }

        if (error.config) {
            const requestKey = `${error.config.url}-${error.config.method}`;
            cancelTokenSources.delete(requestKey);
        }

        store.dispatch("stopLoading");

        throw error;
    }
);

export default axiosClient;
