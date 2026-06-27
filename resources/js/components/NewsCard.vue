<script setup>
import { computed } from 'vue';

const props = defineProps({
    article: {
        type: Object,
        required: true,
    },
});

// Trim the excerpt to a hard 150-character ceiling regardless of source length.
const shortExcerpt = computed(() => {
    const text = props.article.excerpt || '';
    return text.length > 150 ? `${text.slice(0, 150).trimEnd()}…` : text;
});

const formattedDate = computed(() => {
    if (!props.article.published_at) return '';
    return new Date(props.article.published_at).toLocaleDateString('uk-UA', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

// Deterministic accent gradient for the placeholder when no image is set.
const placeholderHue = computed(() => {
    const id = Number(props.article.id) || 0;
    return (id * 47) % 360;
});
</script>

<template>
    <article class="news-card h-100">
        <div class="news-card__media">
            <img
                v-if="article.image_url"
                :src="article.image_url"
                :alt="article.title"
                class="news-card__img"
                loading="lazy"
            />
            <div
                v-else
                class="news-card__placeholder"
                :style="{
                    background: `linear-gradient(135deg, hsl(${placeholderHue}, 60%, 22%), #16213e)`,
                }"
            >
                <span class="news-card__placeholder-text">F4X</span>
            </div>
        </div>

        <div class="news-card__body">
            <h2 class="news-card__title">{{ article.title }}</h2>
            <p class="news-card__excerpt">{{ shortExcerpt }}</p>

            <div class="news-card__meta">
                <span class="news-card__author">{{ article.author }}</span>
                <span class="news-card__dot" aria-hidden="true">•</span>
                <time class="news-card__date" :datetime="article.published_at">{{ formattedDate }}</time>
            </div>
        </div>
    </article>
</template>
