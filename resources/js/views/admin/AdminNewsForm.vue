<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api';

const route = useRoute();
const router = useRouter();

const isEdit = computed(() => route.name === 'admin-news-edit');
const slug = computed(() => route.params.slug);

const form = reactive({
    title: '',
    author: '',
    excerpt: '',
    content: '',
    published_at: '',
});

const errors = reactive({});
const loading = ref(false);     // initial fetch (edit mode)
const submitting = ref(false);
const loadError = ref('');
const formError = ref('');

// Convert an ISO timestamp into the value a <input type="datetime-local">
// expects (YYYY-MM-DDTHH:mm) in local time.
const toLocalInput = (iso) => {
    if (!iso) return '';
    const d = new Date(iso);
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

const loadArticle = async () => {
    loading.value = true;
    loadError.value = '';
    try {
        const { data } = await api.get(`/news/${slug.value}`);
        const a = data.data;
        form.title = a.title ?? '';
        form.author = a.author ?? '';
        form.excerpt = a.excerpt ?? '';
        form.content = a.content ?? '';
        form.published_at = toLocalInput(a.published_at);
    } catch (e) {
        loadError.value = 'Не вдалося завантажити новину для редагування.';
    } finally {
        loading.value = false;
    }
};

const validate = () => {
    Object.keys(errors).forEach((key) => delete errors[key]);
    if (!form.title.trim()) errors.title = 'Вкажіть заголовок.';
    if (!form.author.trim()) errors.author = 'Вкажіть автора.';
    if (!form.content.trim()) errors.content = 'Додайте текст новини.';
    if (form.excerpt && form.excerpt.length > 160) errors.excerpt = 'Не більше 160 символів.';
    return Object.keys(errors).length === 0;
};

const handleSubmit = async () => {
    formError.value = '';
    if (!validate()) return;
    submitting.value = true;

    const payload = {
        title: form.title.trim(),
        author: form.author.trim(),
        excerpt: form.excerpt.trim() || null,
        content: form.content,
        published_at: form.published_at || null,
    };

    try {
        if (isEdit.value) {
            await api.put(`/news/${slug.value}`, payload);
        } else {
            await api.post('/news', payload);
        }
        router.push({ name: 'admin-news' });
    } catch (error) {
        const responseErrors = error.response?.data?.errors;
        if (responseErrors) {
            Object.entries(responseErrors).forEach(([key, messages]) => {
                errors[key] = Array.isArray(messages) ? messages[0] : messages;
            });
        } else {
            formError.value = 'Не вдалося зберегти новину. Спробуйте ще раз.';
        }
    } finally {
        submitting.value = false;
    }
};

onMounted(() => {
    if (isEdit.value) loadArticle();
});
</script>

<template>
    <section class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9">
                <header class="page-head d-flex align-items-center justify-content-between gap-3 mb-4">
                    <h1 class="page-head__title h2 mb-0">
                        {{ isEdit ? 'Редагувати новину' : 'Нова новина' }}
                    </h1>
                    <RouterLink :to="{ name: 'admin-news' }" class="btn btn-outline-light btn-sm">
                        ← До списку
                    </RouterLink>
                </header>

                <div v-if="loading" class="text-center py-5">
                    <div class="spinner-border text-accent" role="status">
                        <span class="visually-hidden">Завантаження…</span>
                    </div>
                </div>

                <div v-else-if="loadError" class="text-center py-5">
                    <p class="text-danger mb-3">{{ loadError }}</p>
                    <button class="btn btn-accent" @click="loadArticle">Спробувати знову</button>
                </div>

                <form v-else class="contact-form info-panel" novalidate @submit.prevent="handleSubmit">
                    <div v-if="formError" class="alert alert-danger" role="alert">{{ formError }}</div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Заголовок</label>
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': errors.title }"
                            placeholder="Заголовок новини"
                        />
                        <div v-if="errors.title" class="invalid-feedback">{{ errors.title }}</div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="author" class="form-label">Автор</label>
                            <input
                                id="author"
                                v-model="form.author"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.author }"
                                placeholder="Ім'я автора"
                            />
                            <div v-if="errors.author" class="invalid-feedback">{{ errors.author }}</div>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label for="published_at" class="form-label">
                                Дата публікації <span class="text-secondary">(необов'язково)</span>
                            </label>
                            <input
                                id="published_at"
                                v-model="form.published_at"
                                type="datetime-local"
                                class="form-control"
                                :class="{ 'is-invalid': errors.published_at }"
                            />
                            <div v-if="errors.published_at" class="invalid-feedback">{{ errors.published_at }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="excerpt" class="form-label">
                            Короткий опис <span class="text-secondary">(необов'язково, до 160 символів)</span>
                        </label>
                        <textarea
                            id="excerpt"
                            v-model="form.excerpt"
                            rows="2"
                            maxlength="160"
                            class="form-control"
                            :class="{ 'is-invalid': errors.excerpt }"
                            placeholder="Залиште порожнім — згенерується з тексту."
                        ></textarea>
                        <div v-if="errors.excerpt" class="invalid-feedback">{{ errors.excerpt }}</div>
                    </div>

                    <div class="mb-4">
                        <label for="content" class="form-label">Текст новини <span class="text-secondary">(Markdown)</span></label>
                        <textarea
                            id="content"
                            v-model="form.content"
                            rows="14"
                            class="form-control"
                            :class="{ 'is-invalid': errors.content }"
                            placeholder="Повний текст новини…"
                        ></textarea>
                        <div v-if="errors.content" class="invalid-feedback">{{ errors.content }}</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-accent" :disabled="submitting">
                            <span
                                v-if="submitting"
                                class="spinner-border spinner-border-sm me-2"
                                role="status"
                                aria-hidden="true"
                            ></span>
                            {{ submitting ? 'Збереження…' : (isEdit ? 'Зберегти зміни' : 'Створити новину') }}
                        </button>
                        <RouterLink :to="{ name: 'admin-news' }" class="btn btn-outline-light">Скасувати</RouterLink>
                    </div>

                    <p v-if="!isEdit" class="text-secondary small mt-3 mb-0">
                        Обкладинку буде згенеровано автоматично у фоновому режимі після створення.
                    </p>
                </form>
            </div>
        </div>
    </section>
</template>
