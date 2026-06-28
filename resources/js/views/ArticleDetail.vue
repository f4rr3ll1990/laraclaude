<script setup>
import { computed, ref, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { marked } from 'marked';
import api from '../api';
import NewsCard from '../components/NewsCard.vue';

const route = useRoute();
const router = useRouter();

const article = ref(null);
const related = ref([]);
const loading = ref(false);
const error = ref(null);
const notFound = ref(false);

const appName = import.meta.env.VITE_APP_NAME || 'F4X';

// Configure marked for safe rendering
marked.setOptions({
    breaks: true,
    gfm: true,
});

// Parse markdown content to HTML
const htmlContent = computed(() => {
    const markdown = article.value?.content || '';
    return marked.parse(markdown);
});

const formattedDate = computed(() => {
    if (!article.value?.published_at) return '';
    return new Date(article.value.published_at).toLocaleDateString('uk-UA', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

// Deterministic accent gradient for the hero when no image is set (mirrors NewsCard).
const placeholderHue = computed(() => {
    const id = Number(article.value?.id) || 0;
    return (id * 47) % 360;
});

const fetchRelated = async (slug) => {
    try {
        const { data } = await api.get('/news', {
            params: { per_page: 3, exclude_slug: slug },
        });
        related.value = data.data;
    } catch (e) {
        // Non-fatal: just hide the section if related articles fail to load.
        related.value = [];
    }
};

const fetchArticle = async (slug) => {
    loading.value = true;
    error.value = null;
    notFound.value = false;
    article.value = null;
    related.value = [];

    try {
        const { data } = await api.get(`/news/${slug}`);
        article.value = data.data;
        document.title = `${article.value.title} - ${appName}`;
        fetchRelated(slug);
    } catch (e) {
        if (e.response?.status === 404) {
            notFound.value = true;
            document.title = `Новину не знайдено - ${appName}`;
        } else {
            error.value = 'Не вдалося завантажити новину. Спробуйте ще раз.';
        }
    } finally {
        loading.value = false;
    }
};

const goBack = () => {
    // Return to the previous page if there's history, else fall back to home.
    if (window.history.length > 1) {
        router.back();
    } else {
        router.push('/');
    }
};

// React to slug changes so navigating between articles refetches.
watch(
    () => route.params.slug,
    (slug) => {
        if (slug) fetchArticle(slug);
    },
    { immediate: true }
);
</script>

<template>
    <section class="container py-5">
        <!-- Loading skeleton -->
        <div v-if="loading" class="article-skeleton mx-auto">
            <div class="skeleton article-skeleton__hero mb-4"></div>
            <div class="skeleton article-skeleton__title mb-3"></div>
            <div class="skeleton article-skeleton__meta mb-4"></div>
            <div class="skeleton article-skeleton__line mb-2"></div>
            <div class="skeleton article-skeleton__line mb-2"></div>
            <div class="skeleton article-skeleton__line article-skeleton__line--short mb-2"></div>
        </div>

        <!-- Not found -->
        <div v-else-if="notFound" class="not-found text-center py-5">
            <p class="not-found__code">404</p>
            <h1 class="not-found__title">Новину не знайдено</h1>
            <p class="text-secondary mb-4">
                Новина, яку ви шукаєте, не існує або була видалена.
            </p>
            <RouterLink class="btn btn-accent btn-lg px-4" to="/">Повернутися на головну</RouterLink>
        </div>

        <!-- Error with retry -->
        <div v-else-if="error" class="text-center py-5">
            <p class="text-danger mb-3">{{ error }}</p>
            <button class="btn btn-accent" @click="fetchArticle(route.params.slug)">Спробувати знову</button>
        </div>

        <!-- Loaded article -->
        <article v-else-if="article" class="article-detail">
            <!-- Hero banner -->
            <div class="article-hero mb-4">
                <img
                    v-if="article.image_url"
                    :src="article.image_url"
                    :alt="article.title"
                    class="article-hero__img"
                />
                <div
                    v-else
                    class="article-hero__img article-hero__placeholder"
                    :style="{
                        background: `linear-gradient(135deg, hsl(${placeholderHue}, 60%, 22%), #16213e)`,
                    }"
                >
                    <span class="article-hero__placeholder-text">F4X</span>
                </div>
                <div class="article-hero__overlay"></div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8 article-detail__column">
                    <button class="article-back btn btn-link px-0 mb-3" @click="goBack">
                        ← Назад
                    </button>

                    <h1 class="page-head__title article-detail__title mb-3">{{ article.title }}</h1>

                    <div class="article-detail__meta mb-4">
                        <span class="article-detail__author">{{ article.author }}</span>
                        <span class="article-detail__dot" aria-hidden="true">•</span>
                        <time class="article-detail__date" :datetime="article.published_at">
                            {{ formattedDate }}
                        </time>
                    </div>

                    <!-- Social sharing (visual only) -->
                    <div class="article-share mb-4" aria-label="Поділитися цією новиною">
                        <button class="share-btn" type="button" aria-label="Поділитися в X" @click.prevent>
                            <span aria-hidden="true">𝕏</span>
                        </button>
                        <button class="share-btn" type="button" aria-label="Поділитися у Facebook" @click.prevent>
                            <span aria-hidden="true">f</span>
                        </button>
                        <button class="share-btn" type="button" aria-label="Копіювати посилання" @click.prevent>
                            <span aria-hidden="true">🔗</span>
                        </button>
                    </div>

                    <div class="prose article-body" v-html="htmlContent"></div>
                </div>
            </div>

            <!-- Related articles -->
            <section v-if="related.length" class="article-related mt-5">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <h2 class="section-title mb-4">Схожі новини</h2>
                        <div class="row g-4">
                            <div
                                v-for="item in related"
                                :key="item.id"
                                class="col-12 col-md-4"
                            >
                                <RouterLink
                                    :to="`/news/${item.slug}`"
                                    class="news-card-link text-decoration-none d-block h-100"
                                >
                                    <NewsCard :article="item" />
                                </RouterLink>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </article>
    </section>
</template>
