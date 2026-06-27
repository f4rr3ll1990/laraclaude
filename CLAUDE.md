# CLAUDE.md

Project context for Claude Code working in this repository.

## What this is

**F4X** — a dark-themed, minimalist news website. Laravel serves a JSON
API plus a single Blade shell that boots a Vue 3 SPA. All page navigation happens
client-side via Vue Router; all data comes from `/api/news`.

## Architecture

- **SPA model.** `routes/web.php` has one catch-all route that returns the
  `app` Blade view for every non-`/api` path, so deep links and refreshes work.
  Vue Router owns `/`, `/about`, `/contacts`, and a `*` 404.
- **API.** `routes/api.php` is registered in `bootstrap/app.php` (`api:` key).
  `NewsController@index` returns a 10-per-page paginator ordered by
  `published_at DESC`; `@show` resolves an article by `slug` (404 JSON on miss).
- **Data.** Single `news` table / `News` model (`$table = 'news'`, slug is the
  route key, `published_at` cast to datetime). `NewsSeeder` generates 55 articles
  from headline/sentence templates (no external Faker locale needed); `image_url`
  is intentionally `null` so the frontend renders a styled gradient placeholder.
- **Frontend.** Vite + `@vitejs/plugin-vue`. Entry `resources/js/app.js`.
  Bootstrap is imported via SCSS (`resources/css/app.scss`) with dark-palette
  variable overrides *before* the Bootstrap import. Axios instance in
  `resources/js/api.js` uses `import.meta.env.VITE_API_BASE_URL` (default `/api`).

## Key files

| Path | Purpose |
|------|---------|
| `bootstrap/app.php` | Registers `routes/api.php` |
| `routes/web.php` | SPA catch-all (`/{any?}` excluding `api`) |
| `routes/api.php` | `/news`, `/news/{slug}` |
| `app/Http/Controllers/Api/NewsController.php` | API logic |
| `app/Models/News.php` | Model, table `news`, slug route key |
| `database/seeders/NewsSeeder.php` | 55 sample articles |
| `resources/views/app.blade.php` | SPA shell, `data-bs-theme="dark"` |
| `resources/js/router.js` | Vue Router config |
| `resources/css/app.scss` | Dark theme + Bootstrap |

## Conventions

- Vue components use `<script setup>` (Composition API) only.
- Dark palette lives as SCSS variables at the top of `app.scss`
  (`$bg #1a1a2e`, `$text #e0e0e0`, `$accent #00d4ff`). Reuse `.btn-accent`,
  `.info-panel`, `.page-head`, `.text-accent` rather than adding ad-hoc colors.
- Excerpts are capped at 150 chars in `NewsCard.vue` regardless of source length.

## Dev commands

```bash
npm run dev                       # Vite dev server (HMR)
php artisan serve                 # http://127.0.0.1:8000
php artisan migrate:fresh --seed  # reset DB + reseed 55 articles
npm run build                     # production asset build
php artisan route:list            # inspect routes
```

## Database

MySQL, database `laraclaude_news` on `127.0.0.1:3306`, user `root`, empty
password (see `.env`). Sessions/cache use the `file` driver to avoid extra
tables. The `mysql` CLI client may be absent on this machine — create the DB
via PDO (see README) if needed; the server itself is reachable.

## Gotchas

- Building emits Sass *deprecation* warnings from Bootstrap 5's legacy color
  functions under Dart Sass. They are non-fatal — the build succeeds.
- The catch-all web route must not capture `/api/*`; the regex
  `^(?!api).*$` enforces that. Keep API routes in `routes/api.php`.
- There is no real contact backend — `ContactForm.vue` simulates submission.
