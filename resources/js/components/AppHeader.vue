<script setup>
import { ref } from 'vue';
import { RouterLink } from 'vue-router';

const collapsed = ref(true);

const toggle = () => {
    collapsed.value = !collapsed.value;
};

const close = () => {
    collapsed.value = true;
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
                <ul class="navbar-nav ms-auto mb-2 mb-md-0">
                    <li class="nav-item">
                        <RouterLink class="nav-link" to="/" @click="close">Головна</RouterLink>
                    </li>
                    <li class="nav-item">
                        <RouterLink class="nav-link" to="/about" @click="close">Про нас</RouterLink>
                    </li>
                    <li class="nav-item">
                        <RouterLink class="nav-link" to="/contacts" @click="close">Контакти</RouterLink>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>
