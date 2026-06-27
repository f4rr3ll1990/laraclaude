import axios from 'axios';

// Base URL is configurable via Vite env (see .env: VITE_API_BASE_URL).
const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
    headers: {
        Accept: 'application/json',
    },
});

export default api;
