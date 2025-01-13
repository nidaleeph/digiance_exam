import axiosClient from "../axios";

export function getUser({ commit }) {
    return axiosClient.get("/get-user").then(({ data }) => {
        commit("setUser", data.user);
        return data;
    });
}

export function login({ commit }, data) {
    return axiosClient.post("/login", data).then(({ data }) => {
        commit("setUser", data.user);
        commit("setToken", data.token);
        return data;
    });
}

export function logout({ commit }) {
    return axiosClient.post("/logout").then((response) => {
        commit("setToken", null);

        return response;
    });
}

export function register({ commit }, data) {
    return axiosClient.post("/register", data).then(({ data }) => {
        commit("setUser", data.user);
        commit("setToken", data.token);
        return data;
    });
}

export const startLoading = ({ commit }, message = null) => {
    console.log(message, "message");
    commit("SET_LOADING", true);
    commit("SET_LOADING_MESSAGE", message);
};

export const stopLoading = ({ commit }, message = null) => {
    commit("SET_LOADING", false);
    commit("SET_LOADING_MESSAGE", message);
};

export const toCurrency = async (
    { commit },
    { amount, currency, locale = "en-US" }
) => {
    const formattedCurrency = new Intl.NumberFormat(locale, {
        style: "currency",
        currency,
    }).format(amount);

    // Optionally, you can commit or return the result
    return formattedCurrency;
};
