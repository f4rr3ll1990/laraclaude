<script setup>
import { RouterLink } from 'vue-router';

const year = new Date().getFullYear();
</script>

<template>
    <footer class="app-footer mt-5">
        <div class="container py-4">
            <div class="row gy-3 align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <span class="brand-text fw-semibold">F4X</span>
                    <p class="text-secondary small mb-0 mt-1">
                        Мінімалістичні новини для зв'язаного світу.
                    </p>
                </div>
                <div class="col-md-6">
                    <ul class="nav justify-content-center justify-content-md-end gap-2">
                        <li class="nav-item">
                            <RouterLink class="nav-link px-2 py-0" to="/">Головна</RouterLink>
                        </li>
                        <li class="nav-item">
                            <RouterLink class="nav-link px-2 py-0" to="/about">Про нас</RouterLink>
                        </li>
                        <li class="nav-item">
                            <RouterLink class="nav-link px-2 py-0" to="/contacts">Контакти</RouterLink>
                        </li>
                    </ul>
                </div>
            </div>
            <hr class="footer-divider my-3" />
            <p class="text-secondary small text-center mb-0">
                &copy; {{ year }} F4X. Всі права захищено.
            </p>
        </div>
    </footer>
</template>
