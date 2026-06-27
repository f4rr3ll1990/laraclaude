<script setup>
import AppHeader from './components/AppHeader.vue';
import AppFooter from './components/AppFooter.vue';
</script>

<template>
    <div class="app-shell d-flex flex-column min-vh-100">
        <AppHeader />

        <main class="flex-grow-1">
            <router-view v-slot="{ Component }">
                <transition name="fade" mode="out-in">
                    <component :is="Component" />
                </transition>
            </router-view>
        </main>

        <AppFooter />
    </div>
</template>
