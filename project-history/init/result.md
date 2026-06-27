##Nightwire News — Laravel 13 + Vue 3 + Bootstrap 5 (dark)
###Backend

news table migration (title, slug, excerpt, content, image_url, author, published_at + timestamps), News model with slug route-key and datetime cast
NewsSeeder generating 55 realistic articles (verified seeded)
NewsController: GET /api/news (10/page, published_at DESC, ?page=) and GET /api/news/{slug} (404 JSON on miss)
API routes registered in bootstrap/app.php; web.php catch-all serves the SPA for deep links/refresh while excluding /api
Frontend (Vue 3, Composition API, <script setup>)

router.js (Home/About/Contacts + * 404, page transitions, dynamic titles), api.js (axios using VITE_API_BASE_URL)
Components: AppHeader (sticky dark navbar), AppFooter, NewsCard (placeholder, 150-char excerpt cap, hover glow), ContactForm (client validation + simulated submit)
Views: Home (2-col grid, Load-More with loading/empty/error states + "No more articles"), About, Contacts, NotFound
app.scss: Bootstrap dark theme overrides (#1a1a2e / #e0e0e0 / #00d4ff), gradients, card hover, timeline, transitions
Docs: README.md (setup + API reference) and CLAUDE.md.

Verified live: npm run build succeeds; /api/news returns 10 items DESC-ordered with paginator meta; ?page=2 differs; single-slug returns full content; bad slug → 404; / serves the dark SPA shell with Vite assets; /about and unknown routes → 200 (Vue handles 404 client-side).

###To run it:


npm run dev          # terminal 1 (HMR)
php artisan serve    # terminal 2 → http://127.0.0.1:8000
One note: the build emits Sass deprecation warnings from Bootstrap 5's legacy color functions under the current Dart Sass — they're non-fatal and the build completes cleanly.