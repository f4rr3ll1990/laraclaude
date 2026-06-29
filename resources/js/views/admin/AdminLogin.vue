<script setup>
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { login } from '../../auth';

const route = useRoute();
const router = useRouter();

const form = reactive({ email: '', password: '' });
const errors = reactive({});
const submitting = ref(false);
const formError = ref('');

const handleSubmit = async () => {
    formError.value = '';
    Object.keys(errors).forEach((key) => delete errors[key]);
    submitting.value = true;

    try {
        const user = await login(form.email.trim(), form.password);

        if (!user?.is_admin) {
            formError.value = 'Цей обліковий запис не має прав адміністратора.';
            return;
        }

        const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : null;
        router.replace(redirect || { name: 'admin-news' });
    } catch (error) {
        // Map Laravel 422 validation errors onto fields; show a generic
        // message for anything else (e.g. wrong credentials surface as 422 too).
        const responseErrors = error.response?.data?.errors;
        if (responseErrors) {
            Object.entries(responseErrors).forEach(([key, messages]) => {
                errors[key] = Array.isArray(messages) ? messages[0] : messages;
            });
        } else {
            formError.value = 'Не вдалося увійти. Спробуйте ще раз пізніше.';
        }
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <section class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-7 col-lg-5">
                <header class="page-head mb-4">
                    <h1 class="page-head__title h2">Адмінпанель</h1>
                    <p class="page-head__subtitle text-secondary mb-0">
                        Увійдіть, щоб керувати новинами.
                    </p>
                </header>

                <form class="contact-form info-panel" novalidate @submit.prevent="handleSubmit">
                    <div v-if="formError" class="alert alert-danger" role="alert">
                        {{ formError }}
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Електронна пошта</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="form-control"
                            :class="{ 'is-invalid': errors.email }"
                            placeholder="admin@f4x.test"
                            autocomplete="username"
                        />
                        <div v-if="errors.email" class="invalid-feedback">{{ errors.email }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="form-control"
                            :class="{ 'is-invalid': errors.password }"
                            autocomplete="current-password"
                        />
                        <div v-if="errors.password" class="invalid-feedback">{{ errors.password }}</div>
                    </div>

                    <button type="submit" class="btn btn-accent w-100" :disabled="submitting">
                        <span
                            v-if="submitting"
                            class="spinner-border spinner-border-sm me-2"
                            role="status"
                            aria-hidden="true"
                        ></span>
                        {{ submitting ? 'Вхід…' : 'Увійти' }}
                    </button>
                </form>
            </div>
        </div>
    </section>
</template>
