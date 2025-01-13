export default {
    user: {
        token: sessionStorage.getItem("TOKEN"),
        data: {},
    },
    loading: {
        isLoading: false,
        message: null,
    },
};
