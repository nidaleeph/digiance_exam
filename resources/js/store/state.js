export default {
    user: {
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
