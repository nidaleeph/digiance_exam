export function setToken(state, token) {
    state.user.token = token;
    if (token) {
        sessionStorage.setItem("TOKEN", token);
    } else {
        sessionStorage.removeItem("TOKEN");
    }
}

export function setUser(state, user) {
    state.user.data = user;
}

export const SET_LOADING = (state, isLoading) => {
    state.loading.isLoading = Boolean(isLoading);
};

export const SET_LOADING_MESSAGE = (state, message) => {
    state.loading.message = typeof message === "string" ? message : null;
};
