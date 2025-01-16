export function setToken(state, token) {
    state.user.token = token;
    if (token) {
        localStorage.setItem("TOKEN", token);
        sessionStorage.removeItem("TOKEN");
    } else {
        localStorage.removeItem("TOKEN");
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
