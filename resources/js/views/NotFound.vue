<script setup>
import { RouterLink } from 'vue-router';
</script>

<template>
    <section class="container py-5">
        <div class="not-found text-center py-5">
            <p class="not-found__code">404</p>
            <h1 class="not-found__title">Сторінку не знайдено</h1>
            <p class="text-secondary mb-4">
                Сторінка, яку ви шукаєте, не існує або була переміщена.
            </p>
            <RouterLink class="btn btn-accent btn-lg px-4" to="/">Повернутися на головну</RouterLink>
        </div>
    </section>
</template>
