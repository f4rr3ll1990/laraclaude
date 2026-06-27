<script setup>
import ContactForm from '../components/ContactForm.vue';

const details = [
    { label: 'Електронна пошта', value: 'hello@f4x.news', icon: '✉' },
    { label: 'Телефон', value: '+1 (555) 018-2049', icon: '☎' },
    { label: 'Адреса', value: '48 Aurora Lane, Suite 12\nPortland, OR 97204', icon: '⌖' },
];
</script>

<template>
    <section class="container py-5">
        <header class="page-head mb-5">
            <h1 class="page-head__title">Зв'язатися з нами</h1>
            <p class="page-head__subtitle text-secondary">
                Запитання, поради чи відгуки? Ми будемо раді почути вас.
            </p>
        </header>

        <div class="row g-5 justify-content-center">
            <div class="col-lg-7">
                <div class="info-panel">
                    <h2 class="info-panel__title mb-4">Надіслати повідомлення</h2>
                    <ContactForm />
                </div>
            </div>

            <div class="col-lg-5">
                <div class="info-panel mb-4">
                    <h2 class="info-panel__title mb-4">Контактна інформація</h2>
                    <ul class="contact-list list-unstyled mb-0">
                        <li v-for="item in details" :key="item.label" class="contact-list__item">
                            <span class="contact-list__icon" aria-hidden="true">{{ item.icon }}</span>
                            <div>
                                <span class="contact-list__label">{{ item.label }}</span>
                                <span class="contact-list__value">{{ item.value }}</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="info-panel location-panel">
                    <h2 class="info-panel__title mb-2">Як нас знайти</h2>
                    <p class="text-secondary mb-1">Pearl District, Портленд, Орегон</p>
                    <p class="text-secondary small mb-0">
                        За кілька хвилин ходьби від набережної. Працюємо у будні, з 9:00 до 18:00 (PST).
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
