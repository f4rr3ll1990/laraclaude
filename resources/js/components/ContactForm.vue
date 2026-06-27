<script setup>
import { reactive, ref } from 'vue';

const form = reactive({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const errors = reactive({});
const submitting = ref(false);
const submitted = ref(false);

const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const validate = () => {
    Object.keys(errors).forEach((key) => delete errors[key]);

    if (!form.name.trim()) {
        errors.name = "Будь ласка, введіть ваше ім'я.";
    }
    if (!form.email.trim()) {
        errors.email = 'Будь ласка, введіть вашу електронну пошту.';
    } else if (!emailPattern.test(form.email.trim())) {
        errors.email = 'Будь ласка, введіть коректну електронну пошту.';
    }
    if (!form.subject.trim()) {
        errors.subject = 'Будь ласка, введіть тему.';
    }
    if (!form.message.trim()) {
        errors.message = 'Будь ласка, введіть повідомлення.';
    } else if (form.message.trim().length < 10) {
        errors.message = 'Повідомлення має містити щонайменше 10 символів.';
    }

    return Object.keys(errors).length === 0;
};

const handleSubmit = async () => {
    submitted.value = false;
    if (!validate()) return;

    submitting.value = true;

    // Simulate a network request — no backend endpoint required.
    await new Promise((resolve) => setTimeout(resolve, 800));

    submitting.value = false;
    submitted.value = true;

    form.name = '';
    form.email = '';
    form.subject = '';
    form.message = '';
};
</script>

<template>
    <form class="contact-form" novalidate @submit.prevent="handleSubmit">
        <div
            v-if="submitted"
            class="alert alert-success d-flex align-items-center"
            role="alert"
        >
            <span class="me-2">✓</span>
            Дякуємо, що звернулися — ваше повідомлення надіслано. Ми скоро з вами зв'яжемося.
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Ім'я</label>
            <input
                id="name"
                v-model="form.name"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.name }"
                placeholder="Ваше ім'я"
            />
            <div v-if="errors.name" class="invalid-feedback">{{ errors.name }}</div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Електронна пошта</label>
            <input
                id="email"
                v-model="form.email"
                type="email"
                class="form-control"
                :class="{ 'is-invalid': errors.email }"
                placeholder="you@example.com"
            />
            <div v-if="errors.email" class="invalid-feedback">{{ errors.email }}</div>
        </div>

        <div class="mb-3">
            <label for="subject" class="form-label">Тема</label>
            <input
                id="subject"
                v-model="form.subject"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.subject }"
                placeholder="Про що йдеться?"
            />
            <div v-if="errors.subject" class="invalid-feedback">{{ errors.subject }}</div>
        </div>

        <div class="mb-3">
            <label for="message" class="form-label">Повідомлення</label>
            <textarea
                id="message"
                v-model="form.message"
                rows="5"
                class="form-control"
                :class="{ 'is-invalid': errors.message }"
                placeholder="Напишіть ваше повідомлення…"
            ></textarea>
            <div v-if="errors.message" class="invalid-feedback">{{ errors.message }}</div>
        </div>

        <button type="submit" class="btn btn-accent" :disabled="submitting">
            <span
                v-if="submitting"
                class="spinner-border spinner-border-sm me-2"
                role="status"
                aria-hidden="true"
            ></span>
            {{ submitting ? 'Надсилання…' : 'Надіслати повідомлення' }}
        </button>
    </form>
</template>
