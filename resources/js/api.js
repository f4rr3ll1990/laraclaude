import axios from 'axios';

// Base URL is configurable via Vite env (see .env: VITE_API_BASE_URL).
const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
    headers: {
        Accept: 'application/json',
    },
});

// Attach the Sanctum bearer token (set by auth.js) to every request. Read from
// localStorage directly to avoid a circular import with auth.js.
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('f4x_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// On 401 the token is missing/expired: clear it and bounce to the admin login
// (unless we're already there). Other errors propagate to the caller.
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('f4x_token');
            localStorage.removeItem('f4x_user');
            if (!window.location.pathname.startsWith('/admin/login')) {
                window.location.assign('/admin/login');
            }
        }
        return Promise.reject(error);
    }
);

export default api;
