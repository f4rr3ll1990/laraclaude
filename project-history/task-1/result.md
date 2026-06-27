##What changed
###Backend — NewsController.php

index() now accepts optional per_page (1–50, default 10) and exclude_slug query params. Defaults are unchanged, so existing behavior is preserved. show() was already in place.
Frontend

router.js — new /news/:slug → ArticleDetail route, placed before the catch-all.
Home.vue — each NewsCard is now wrapped in a RouterLink, making the whole card clickable.
ArticleDetail.vue (new) — hero banner (gradient placeholder + dark overlay), title, author/date meta, visual-only share buttons (X / Facebook / Copy Link), body rendered as escaped <p> paragraphs (XSS-safe since content is plain text), back button (router.back() with / fallback), and a 3-article Related section. Handles loading skeleton, 404 "Article Not Found", and error+retry states; sets document.title to the article title; watches the slug so navigating between articles refetches.
app.scss — hero, share buttons, skeleton shimmer, fade-in animation, and clickable-card-link styles, all reusing the existing dark-palette variables.
Verified
npm run build succeeds (only the expected, non-fatal Bootstrap Sass deprecation warnings).
API live tests: show returns the article with content; default index still returns 10/page (55 total — regression check); per_page=3&exclude_slug=… returns exactly 3 with the current article excluded; unknown slug returns HTTP 404.
The page-level fade transition in App.vue applies automatically to the new route, and existing pages (Load More, About, Contacts) are untouched. To see it in the browser: npm run dev + php artisan serve, then click any card.