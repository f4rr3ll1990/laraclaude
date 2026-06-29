import { reactive, computed } from 'vue';
import api from './api';

// Auth state for the admin panel. Lightweight module-level reactive store
// (no Pinia) — the token is sent on every request by the axios interceptor in
// api.js, which reads the same localStorage key written here.
const TOKEN_KEY = 'f4x_token';
const USER_KEY = 'f4x_user';

const state = reactive({
    token: localStorage.getItem(TOKEN_KEY) || null,
    user: JSON.parse(localStorage.getItem(USER_KEY) || 'null'),
});

export const isAuthenticated = computed(() => !!state.token);
export const isAdmin = computed(() => !!state.user?.is_admin);
export const currentUser = computed(() => state.user);

function persist(token, user) {
    state.token = token;
    state.user = user;
    localStorage.setItem(TOKEN_KEY, token);
    localStorage.setItem(USER_KEY, JSON.stringify(user));
}

// Drop all auth state. Exported so the axios 401 interceptor can call it.
export function clearAuth() {
    state.token = null;
    state.user = null;
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
}

export async function login(email, password) {
    const { data } = await api.post('/login', { email, password });
    persist(data.token, data.user);
    return data.user;
}

export async function logout() {
    try {
        await api.post('/logout');
    } catch {
        // Token may already be invalid server-side; clear locally regardless.
    }
    clearAuth();
}
