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
  from headline/sentence templates (no external Faker locale needed); seeded
  `image_url` is `null` so the frontend renders a styled gradient placeholder.
- **Article creation + AI image.** `POST /api/news` (Sanctum-protected,
  `NewsController@store`) saves the article immediately with `image_url = null`
  and dispatches a queued `GenerateArticleImage` job. The job asks **Puter**
  (`puter.ai.txt2img`, default `gpt-image-2`) for a cover (the article **title**
  is the prompt), stores it on the `public` disk, and writes the URL back to
  `image_url`. See [Image generation (Puter)](#image-generation-puter).
- **Frontend.** Vite + `@vitejs/plugin-vue`. Entry `resources/js/app.js`.
  Bootstrap is imported via SCSS (`resources/css/app.scss`) with dark-palette
  variable overrides *before* the Bootstrap import. Axios instance in
  `resources/js/api.js` uses `import.meta.env.VITE_API_BASE_URL` (default `/api`).

## Key files

| Path | Purpose |
|------|---------|
| `bootstrap/app.php` | Registers `routes/api.php` |
| `routes/web.php` | SPA catch-all (`/{any?}` excluding `api`) |
| `routes/api.php` | `GET /news`, `GET /news/{slug}`, `POST /news` (Sanctum), auth routes |
| `app/Http/Controllers/Api/NewsController.php` | API logic |
| `app/Http/Requests/StoreNewsRequest.php` | `POST /news` validation rules |
| `app/Jobs/GenerateArticleImage.php` | Queued cover-image generation per article |
| `app/Services/PuterImageService.php` | Runs the Node bridge, returns image bytes |
| `app/Services/GeminiImageService.php` | Legacy Gemini wrapper (no longer wired up) |
| `ptr_img_gen/generate.js` | Node bridge: `puter.ai.txt2img` → base64 JSON on stdout |
| `ptr_img_gen/auth.js` | One-time browser auth → prints `PUTER_AUTH_TOKEN` |
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

## Public run (f4x.pp.ua)

The site is served publicly via Cloudflare:
`browser → Cloudflare → white IP :80 (router forward) → host :8000`.

```bash
./start.sh          # check/start MySQL (LAMPP), build assets, serve on 0.0.0.0:8000
./stop.sh           # stop artisan serve
./stop.sh --mysql   # also stop MySQL
```

- `start.sh` removes `public/hot` and ensures `public/build` exists so `@vite`
  serves hashed assets (not dev-server URLs); PID in `storage/serve.pid`,
  log in `storage/logs/serve.log`. It also ensures the `public/storage` symlink
  and launches a `php artisan queue:work` worker (PID `storage/queue.pid`, log
  `storage/logs/queue.log`) — required for cover images to generate. `stop.sh`
  stops both serve and worker.
- artisan **must** bind `0.0.0.0` (not `127.0.0.1`) to be reachable from the router.
- Cloudflare **SSL/TLS mode must be `Flexible`** — only port 80 is forwarded, so
  `Full`/`Full (strict)` tries origin :443 and returns **error 521**.
- Public `.env`: `APP_ENV=production`, `APP_URL=https://f4x.pp.ua`. MySQL is
  XAMPP/LAMPP at `/opt/lampp` (start needs sudo).

## Database

MySQL, database `laraclaude_news` on `127.0.0.1:3306`, user `root`, empty
password (see `.env`). Sessions/cache use the `file` driver to avoid extra
tables. The `mysql` CLI client may be absent on this machine — create the DB
via PDO (see README) if needed; the server itself is reachable.

## Image generation (Puter)

`POST /api/news` generates an article cover via **Puter** (`puter.ai.txt2img`),
using the article **`title`** as the prompt. Puter's free image API replaced
Gemini (which hit `limit: 0` quota on the free tier).

- **Why a Node bridge.** Puter's SDK (`@heyputer/puter.js`) only runs in a
  browser or Node — there is no PHP client. So `PuterImageService` shells out
  (via Laravel's `Process`) to `ptr_img_gen/generate.js`, which calls
  `puter.ai.txt2img(title, { model, quality })` and prints
  `{"mime":"…","data":"<base64>"}` to stdout. The service decodes that to bytes.
- **Auth is a saved token, set up once.** Puter requires a browser login. Run
  `node ptr_img_gen/auth.js` once — it opens a browser, and after you confirm it
  **prints an auth token to the console**. Copy that token into `.env` as
  `PUTER_AUTH_TOKEN`. Every later generation reuses it headlessly (no browser);
  re-run `auth.js` if it expires/is revoked.
- **Flow.** `NewsController@store` saves the article with `image_url = null` and
  dispatches `GenerateArticleImage` (queue `database`). The job calls
  `PuterImageService::generate($title)`, stores the bytes at
  `storage/app/public/articles/{id}.{ext}`, and sets `image_url` to the
  `Storage::disk('public')->url(...)` value (`{APP_URL}/storage/articles/...`).
- **Requires a running worker.** Jobs sit in the `jobs` table until
  `php artisan queue:work` (or `--once`) processes them. `start.sh` launches one;
  for local dev run it yourself. Without a worker, `image_url` stays `null`.
- **Storage symlink.** `php artisan storage:link` must exist (`start.sh` ensures
  it) or the generated URLs 404.
- **Config.** `PUTER_AUTH_TOKEN` in `.env`; model/quality overridable via
  `PUTER_IMAGE_MODEL` (default `gpt-image-2`) and `PUTER_IMAGE_QUALITY` (default
  `low`); Node binary via `NODE_BINARY`. All read through `config/services.php`
  → `services.puter`. The bridge's `node_modules` live in `ptr_img_gen/`.
- **Graceful failure.** Any error (missing/expired token, timeout, no image) is
  logged to `storage/logs/laravel.log` and the article keeps `image_url = null`
  — the frontend placeholder covers it. Article creation never fails on image
  errors.
- **Endpoint is `auth:sanctum`.** Get a Bearer token via `POST /api/register`
  or `/api/login`, then send it on `POST /api/news`.

## Gotchas

- Building emits Sass *deprecation* warnings from Bootstrap 5's legacy color
  functions under Dart Sass. They are non-fatal — the build succeeds.
- The catch-all web route must not capture `/api/*`; the regex
  `^(?!api).*$` enforces that. Keep API routes in `routes/api.php`.
- There is no real contact backend — `ContactForm.vue` simulates submission.
- Puter image generation needs a valid `PUTER_AUTH_TOKEN` (see
  [Image generation (Puter)](#image-generation-puter)). On `Unauthorized` the
  token has expired — re-run `node ptr_img_gen/auth.js`. Articles are still
  created on any failure, just with `image_url = null`.
- The Node bridge needs `node` on the worker's PATH and the deps in
  `ptr_img_gen/node_modules` (`npm install` in that dir). `puter.js` opens a
  background socket that may emit an unhandled-rejection after the image is
  returned; `generate.js` swallows it so it never masks a good result.
