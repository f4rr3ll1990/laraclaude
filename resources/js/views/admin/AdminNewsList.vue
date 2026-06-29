<script setup>
import { onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import api from '../../api';

const articles = ref([]);
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);
const loading = ref(false);
const error = ref(null);
const busySlug = ref(null); // slug currently being deleted / regenerated

const formatDate = (value) => {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('uk-UA', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const fetchPage = async (page) => {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await api.get('/news', { params: { page, per_page: 20 } });
        articles.value = data.data;
        currentPage.value = data.current_page;
        lastPage.value = data.last_page;
        total.value = data.total;
    } catch (e) {
        error.value = 'Не вдалося завантажити новини. Спробуйте пізніше.';
    } finally {
        loading.value = false;
    }
};

const goTo = (page) => {
    if (page < 1 || page > lastPage.value || page === currentPage.value) return;
    fetchPage(page);
};

const remove = async (article) => {
    if (!window.confirm(`Видалити «${article.title}»? Цю дію не можна скасувати.`)) return;
    busySlug.value = article.slug;
    try {
        await api.delete(`/news/${article.slug}`);
        // Refetch the current page so counts/pagination stay correct.
        await fetchPage(articles.value.length === 1 && currentPage.value > 1 ? currentPage.value - 1 : currentPage.value);
    } catch (e) {
        window.alert('Не вдалося видалити новину. Спробуйте ще раз.');
    } finally {
        busySlug.value = null;
    }
};

const regenerate = async (article) => {
    busySlug.value = article.slug;
    try {
        const { data } = await api.post(`/news/${article.slug}/regenerate-image`);
        // Reflect the reset image immediately; the worker fills it back in.
        const idx = articles.value.findIndex((a) => a.id === article.id);
        if (idx !== -1) articles.value[idx] = data.data;
    } catch (e) {
        window.alert('Не вдалося запустити генерацію зображення.');
    } finally {
        busySlug.value = null;
    }
};

onMounted(() => fetchPage(1));
</script>

<template>
    <section class="container py-5">
        <header class="page-head d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="page-head__title h2 mb-1">Керування новинами</h1>
                <p class="page-head__subtitle text-secondary mb-0">
                    Усього новин: {{ total }}
                </p>
            </div>
            <RouterLink :to="{ name: 'admin-news-create' }" class="btn btn-accent">
                + Нова новина
            </RouterLink>
        </header>

        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-accent" role="status">
                <span class="visually-hidden">Завантаження…</span>
            </div>
        </div>

        <div v-else-if="error" class="text-center py-5">
            <p class="text-danger mb-3">{{ error }}</p>
            <button class="btn btn-accent" @click="fetchPage(currentPage)">Спробувати знову</button>
        </div>

        <div v-else-if="articles.length === 0" class="text-center py-5">
            <p class="text-secondary mb-0">Поки що немає жодної новини.</p>
        </div>

        <template v-else>
            <div class="info-panel p-0 admin-table-wrap">
                <table class="table table-dark table-hover align-middle mb-0 admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Зображення</th>
                            <th scope="col">Заголовок</th>
                            <th scope="col" class="d-none d-md-table-cell">Автор</th>
                            <th scope="col" class="d-none d-lg-table-cell">Опубліковано</th>
                            <th scope="col" class="text-end">Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="article in articles" :key="article.id">
                            <td>
                                <img
                                    v-if="article.image_url"
                                    :src="article.image_url"
                                    :alt="article.title"
                                    class="admin-thumb"
                                />
                                <span v-else class="admin-thumb admin-thumb--empty">F4X</span>
                            </td>
                            <td>
                                <RouterLink
                                    :to="`/news/${article.slug}`"
                                    class="text-accent text-decoration-none fw-semibold"
                                    target="_blank"
                                >
                                    {{ article.title }}
                                </RouterLink>
                            </td>
                            <td class="d-none d-md-table-cell text-secondary">{{ article.author }}</td>
                            <td class="d-none d-lg-table-cell text-secondary">{{ formatDate(article.published_at) }}</td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <RouterLink
                                        :to="{ name: 'admin-news-edit', params: { slug: article.slug } }"
                                        class="btn btn-sm btn-outline-light"
                                    >
                                        Редагувати
                                    </RouterLink>
                                    <button
                                        class="btn btn-sm btn-outline-info"
                                        :disabled="busySlug === article.slug"
                                        title="Згенерувати зображення заново"
                                        @click="regenerate(article)"
                                    >
                                        <span
                                            v-if="busySlug === article.slug"
                                            class="spinner-border spinner-border-sm"
                                            role="status"
                                            aria-hidden="true"
                                        ></span>
                                        <span v-else>↻ Зображення</span>
                                    </button>
                                    <button
                                        class="btn btn-sm btn-outline-danger"
                                        :disabled="busySlug === article.slug"
                                        @click="remove(article)"
                                    >
                                        Видалити
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <nav v-if="lastPage > 1" class="d-flex justify-content-center align-items-center gap-3 mt-4">
                <button class="btn btn-outline-light btn-sm" :disabled="currentPage <= 1" @click="goTo(currentPage - 1)">
                    ← Назад
                </button>
                <span class="text-secondary">Сторінка {{ currentPage }} з {{ lastPage }}</span>
                <button class="btn btn-outline-light btn-sm" :disabled="currentPage >= lastPage" @click="goTo(currentPage + 1)">
                    Далі →
                </button>
            </nav>
        </template>
    </section>
</template>

<style scoped>
.admin-table-wrap {
    overflow-x: auto;
}
.admin-table {
    --bs-table-bg: transparent;
    --bs-table-color: #e0e0e0;
    --bs-table-hover-bg: rgba(0, 212, 255, 0.05);
    --bs-table-hover-color: #e0e0e0;
    --bs-table-border-color: rgba(255, 255, 255, 0.08);
}
.admin-table thead th {
    color: #9aa3b2;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom-color: rgba(255, 255, 255, 0.12);
}
.admin-thumb {
    width: 64px;
    height: 40px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    display: block;
}
.admin-thumb--empty {
    display: grid;
    place-items: center;
    background: rgba(0, 212, 255, 0.08);
    color: rgba(0, 212, 255, 0.7);
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 1px;
}
</style>
