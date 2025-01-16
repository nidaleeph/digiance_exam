export default {
    user: {
        // Fetch token from localStorage, clear any leftover from sessionStorage
        token: (() => {
            const token = localStorage.getItem("TOKEN");
            sessionStorage.removeItem("TOKEN");
            return token;
        })(),
        data: {},
    },
    loading: {
        isLoading: false,
        message: null,
    },
};
