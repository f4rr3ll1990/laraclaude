<script setup>
import { computed, onMounted, onBeforeUnmount, reactive, ref } from 'vue';
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

// Cover-image preview / regeneration (edit mode only).
const articleId = ref(null);
const imageUrl = ref(null);
const regenerating = ref(false);   // job dispatched, waiting for the worker
const regenError = ref('');
const lightboxOpen = ref(false);
const coverPrompt = ref('');       // editable prompt sent to the renderer
const promptLoading = ref(false);  // asking Gemini for a prompt
const promptNote = ref('');        // info line under the prompt textarea
let pollTimer = null;

// Deterministic placeholder gradient hue, matching NewsCard.vue.
const placeholderHue = computed(() => ((articleId.value || 0) * 47) % 360);

const stopPolling = () => {
    if (pollTimer) {
        clearTimeout(pollTimer);
        pollTimer = null;
    }
};

// After a regeneration is dispatched the worker fills image_url in
// asynchronously, so re-fetch the article a few times until it appears.
const pollForImage = (attempt = 0) => {
    const MAX_ATTEMPTS = 12; // ~36s at 3s intervals
    stopPolling();
    pollTimer = setTimeout(async () => {
        try {
            const { data } = await api.get(`/news/${slug.value}`);
            if (data.data?.image_url) {
                imageUrl.value = data.data.image_url;
                regenerating.value = false;
                return;
            }
        } catch (e) {
            // ignore transient errors and keep polling
        }
        if (attempt + 1 >= MAX_ATTEMPTS) {
            regenerating.value = false;
            regenError.value = 'Зображення ще генерується. Оновіть сторінку трохи згодом.';
            return;
        }
        pollForImage(attempt + 1);
    }, 3000);
};

// Ask Gemini for an image prompt and drop it into the editable textarea so the
// admin can preview / tweak it before rendering.
const requestPrompt = async () => {
    regenError.value = '';
    promptNote.value = '';
    promptLoading.value = true;
    try {
        const { data } = await api.post(`/news/${slug.value}/generate-prompt`);
        coverPrompt.value = data.data?.prompt ?? '';
        promptNote.value = data.data?.source === 'gemini'
            ? 'Промпт згенеровано Gemini. Можна відредагувати перед генерацією.'
            : 'Gemini недоступний — підставлено заголовок як запасний промпт.';
    } catch (e) {
        promptNote.value = 'Не вдалося отримати промпт від Gemini.';
    } finally {
        promptLoading.value = false;
    }
};

const regenerate = async () => {
    regenError.value = '';
    regenerating.value = true;
    imageUrl.value = null;
    // Send the edited prompt when provided; otherwise the job falls back to
    // generating one from the article via Gemini.
    const custom = coverPrompt.value.trim();
    try {
        await api.post(
            `/news/${slug.value}/regenerate-image`,
            custom ? { prompt: custom } : {},
        );
        pollForImage();
    } catch (e) {
        regenerating.value = false;
        regenError.value = 'Не вдалося запустити генерацію зображення.';
    }
};

const openLightbox = () => {
    if (imageUrl.value) lightboxOpen.value = true;
};
const closeLightbox = () => {
    lightboxOpen.value = false;
};

const onKeydown = (e) => {
    if (e.key === 'Escape') closeLightbox();
};

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
        articleId.value = a.id ?? null;
        imageUrl.value = a.image_url ?? null;
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
    window.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    stopPolling();
    window.removeEventListener('keydown', onKeydown);
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

                    <div v-if="isEdit" class="mb-4">
                        <label class="form-label d-block">Обкладинка</label>
                        <div class="cover-edit">
                            <div class="cover-edit__preview">
                                <button
                                    v-if="imageUrl"
                                    type="button"
                                    class="cover-edit__img-btn"
                                    title="Відкрити на весь розмір"
                                    @click="openLightbox"
                                >
                                    <img :src="imageUrl" :alt="form.title" class="cover-edit__img" />
                                    <span class="cover-edit__zoom">⤢</span>
                                </button>
                                <div
                                    v-else
                                    class="cover-edit__placeholder"
                                    :style="{ background: `linear-gradient(135deg, hsl(${placeholderHue}, 60%, 22%), #16213e)` }"
                                >
                                    <span v-if="regenerating" class="spinner-border text-light" role="status">
                                        <span class="visually-hidden">Генерація…</span>
                                    </span>
                                    <span v-else class="cover-edit__placeholder-text">F4X</span>
                                </div>
                            </div>
                            <div class="cover-edit__controls">
                                <label for="cover-prompt" class="form-label small mb-1">
                                    Промпт для генерації <span class="text-secondary">(необов'язково)</span>
                                </label>
                                <textarea
                                    id="cover-prompt"
                                    v-model="coverPrompt"
                                    rows="4"
                                    maxlength="2000"
                                    class="form-control form-control-sm mb-2"
                                    placeholder="Залиште порожнім — промпт згенерує Gemini з тексту новини. Або натисніть «Згенерувати промпт», відредагуйте і генеруйте зображення."
                                ></textarea>

                                <div class="d-flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        class="btn btn-outline-light btn-sm"
                                        :disabled="promptLoading || regenerating"
                                        title="Запросити промпт у Gemini за текстом новини"
                                        @click="requestPrompt"
                                    >
                                        <span
                                            v-if="promptLoading"
                                            class="spinner-border spinner-border-sm me-2"
                                            role="status"
                                            aria-hidden="true"
                                        ></span>
                                        {{ promptLoading ? 'Запит…' : '✨ Згенерувати промпт' }}
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-info btn-sm"
                                        :disabled="regenerating"
                                        @click="regenerate"
                                    >
                                        <span
                                            v-if="regenerating"
                                            class="spinner-border spinner-border-sm me-2"
                                            role="status"
                                            aria-hidden="true"
                                        ></span>
                                        {{ regenerating ? 'Генерація…' : '↻ Перегенерувати зображення' }}
                                    </button>
                                </div>

                                <p v-if="regenError" class="text-warning small mb-0 mt-2">{{ regenError }}</p>
                                <p v-else-if="promptNote" class="text-secondary small mb-0 mt-2">{{ promptNote }}</p>
                                <p v-else class="text-secondary small mb-0 mt-2">
                                    Зображення генерується у фоновому режимі — це може зайняти кілька секунд.
                                </p>
                            </div>
                        </div>
                    </div>

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

        <Teleport to="body">
            <div v-if="lightboxOpen" class="cover-lightbox" @click="closeLightbox">
                <button type="button" class="cover-lightbox__close" aria-label="Закрити" @click="closeLightbox">
                    ✕
                </button>
                <img :src="imageUrl" :alt="form.title" class="cover-lightbox__img" @click.stop />
            </div>
        </Teleport>
    </section>
</template>

<style scoped>
.cover-edit {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-start;
}
.cover-edit__preview {
    flex: 0 0 auto;
}
.cover-edit__img-btn {
    position: relative;
    display: block;
    padding: 0;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    overflow: hidden;
    background: none;
    cursor: zoom-in;
    line-height: 0;
}
.cover-edit__img {
    display: block;
    width: 260px;
    height: 160px;
    object-fit: cover;
}
.cover-edit__zoom {
    position: absolute;
    right: 8px;
    bottom: 8px;
    width: 28px;
    height: 28px;
    display: grid;
    place-items: center;
    border-radius: 6px;
    background: rgba(0, 0, 0, 0.55);
    color: #00d4ff;
    font-size: 0.95rem;
}
.cover-edit__placeholder {
    width: 260px;
    height: 160px;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    display: grid;
    place-items: center;
}
.cover-edit__placeholder-text {
    color: rgba(255, 255, 255, 0.55);
    font-weight: 700;
    letter-spacing: 3px;
}
.cover-edit__controls {
    flex: 1 1 220px;
}

.cover-lightbox {
    position: fixed;
    inset: 0;
    z-index: 1080;
    background: rgba(0, 0, 0, 0.88);
    display: grid;
    place-items: center;
    padding: 2rem;
    cursor: zoom-out;
}
.cover-lightbox__img {
    max-width: 95vw;
    max-height: 90vh;
    object-fit: contain;
    border-radius: 8px;
    cursor: default;
}
.cover-lightbox__close {
    position: absolute;
    top: 1rem;
    right: 1.25rem;
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    color: #e0e0e0;
    font-size: 1.1rem;
    line-height: 1;
    cursor: pointer;
}
.cover-lightbox__close:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>
