<script setup>
import { onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import api from '../api';
import NewsCard from '../components/NewsCard.vue';

const articles = ref([]);
const currentPage = ref(0);
const lastPage = ref(1);
const loading = ref(false);      // initial load
const loadingMore = ref(false);  // "Load More" fetches
const error = ref(null);

const hasMore = () => currentPage.value < lastPage.value;

const fetchPage = async (page) => {
    const { data } = await api.get('/news', { params: { page } });
    // Laravel paginator shape: { data: [...], current_page, last_page, ... }
    articles.value.push(...data.data);
    currentPage.value = data.current_page;
    lastPage.value = data.last_page;
};

const loadInitial = async () => {
    loading.value = true;
    error.value = null;
    try {
        await fetchPage(1);
    } catch (e) {
        error.value = 'Не вдалося завантажити новини. Спробуйте пізніше.';
    } finally {
        loading.value = false;
    }
};

const loadMore = async () => {
    if (loadingMore.value || !hasMore()) return;
    loadingMore.value = true;
    error.value = null;
    try {
        await fetchPage(currentPage.value + 1);
    } catch (e) {
        error.value = 'Не вдалося завантажити більше новин. Спробуйте ще раз.';
    } finally {
        loadingMore.value = false;
    }
};

const retry = () => {
    if (articles.value.length === 0) {
        loadInitial();
    } else {
        loadMore();
    }
};

onMounted(loadInitial);
</script>

<template>
    <section class="container py-5">
        <header class="page-head text-center mb-5">
            <h1 class="page-head__title">Останні новини</h1>
            <p class="page-head__subtitle text-secondary">
                Зрозумілі та лаконічні матеріали про технології, науку та світ.
            </p>
        </header>

        <!-- Initial loading state -->
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-accent" role="status">
                <span class="visually-hidden">Завантаження…</span>
            </div>
            <p class="text-secondary mt-3 mb-0">Завантаження останніх новин…</p>
        </div>

        <!-- Error on initial load with no articles yet -->
        <div v-else-if="error && articles.length === 0" class="text-center py-5">
            <p class="text-danger mb-3">{{ error }}</p>
            <button class="btn btn-accent" @click="retry">Спробувати знову</button>
        </div>

        <!-- Empty state -->
        <div v-else-if="articles.length === 0" class="text-center py-5">
            <p class="text-secondary mb-0">Поки що не опубліковано жодної новини.</p>
        </div>

        <!-- Article grid: 2 columns on desktop, 1 on mobile -->
        <template v-else>
            <div class="row g-4">
                <div
                    v-for="article in articles"
                    :key="article.id"
                    class="col-12 col-lg-6"
                >
                    <RouterLink
                        :to="`/news/${article.slug}`"
                        class="news-card-link text-decoration-none d-block h-100"
                    >
                        <NewsCard :article="article" />
                    </RouterLink>
                </div>
            </div>

            <!-- Inline error during "load more" -->
            <p v-if="error" class="text-danger text-center mt-4 mb-0">{{ error }}</p>

            <div class="text-center mt-5">
                <button
                    v-if="hasMore()"
                    class="btn btn-accent btn-lg px-4"
                    :disabled="loadingMore"
                    @click="loadMore"
                >
                    <span
                        v-if="loadingMore"
                        class="spinner-border spinner-border-sm me-2"
                        role="status"
                        aria-hidden="true"
                    ></span>
                    {{ loadingMore ? 'Завантаження…' : 'Завантажити ще' }}
                </button>
                <p v-else class="text-secondary mb-0">Більше немає новин</p>
            </div>
        </template>
    </section>
</template>
