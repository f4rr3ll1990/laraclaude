<script setup>
import { ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { isAuthenticated, isAdmin, logout } from '../auth';

const router = useRouter();
const collapsed = ref(true);

const toggle = () => {
    collapsed.value = !collapsed.value;
};

const close = () => {
    collapsed.value = true;
};

const handleLogout = async () => {
    close();
    await logout();
    router.push({ name: 'admin-login' });
};
</script>

<template>
    <nav class="navbar navbar-expand-md sticky-top app-navbar">
        <div class="container">
            <RouterLink class="navbar-brand d-flex align-items-center gap-2" to="/" aria-label="Головна F4X" @click="close">
                <span class="brand-mark"></span>
                <span class="brand-text">F4X</span>
            </RouterLink>

            <button
                class="navbar-toggler border-0"
                type="button"
                aria-label="Перемкнути навігацію"
                @click="toggle"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" :class="{ show: !collapsed }">
                <ul class="navbar-nav ms-auto mb-2 mb-md-0 align-items-md-center">
                    <li class="nav-item">
                        <RouterLink class="nav-link" to="/" @click="close">Головна</RouterLink>
                    </li>
                    <li class="nav-item">
                        <RouterLink class="nav-link" to="/about" @click="close">Про нас</RouterLink>
                    </li>
                    <li class="nav-item">
                        <RouterLink class="nav-link" to="/contacts" @click="close">Контакти</RouterLink>
                    </li>
                    <li v-if="isAuthenticated && isAdmin" class="nav-item">
                        <RouterLink class="nav-link" to="/admin" @click="close">Адмінпанель</RouterLink>
                    </li>
                    <li v-if="isAuthenticated && isAdmin" class="nav-item">
                        <button type="button" class="nav-link btn btn-link logout-link" @click="handleLogout">
                            Вихід
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<style scoped>
.logout-link {
    border: 0;
    background: none;
    padding: 0.5rem 0.5rem;
    text-decoration: none;
}
</style>
