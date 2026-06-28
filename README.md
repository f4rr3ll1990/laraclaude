# F4X

**F4X** is a dark-themed, minimalist news website built as a **Laravel** JSON API with a **Vue 3** single-page-application frontend, styled with **Bootstrap 5** (built-in dark mode).

- 🌒 Dark, distraction-free design (`#1a1a2e` background, `#00d4ff` accent)
- 📰 Card-based news grid with a "Load More" infinite-style pagination
- ⚡ Vue 3 (Composition API) + Vue Router SPA, served by Laravel
- 🔌 RESTful JSON API with paginated articles
- 📱 Fully responsive (2 columns desktop / 1 column mobile)

---

## Tech Stack

| Layer      | Technology                                   |
|------------|----------------------------------------------|
| Backend    | Laravel 13 (PHP 8.2+)                         |
| Frontend   | Vue 3 (Composition API), Vue Router 4         |
| Styling    | Bootstrap 5 (SCSS, `data-bs-theme="dark"`)    |
| HTTP       | Axios                                         |
| Build tool | Vite + `laravel-vite-plugin`                  |
| Database   | MySQL                                         |

---

## Prerequisites

- PHP **8.2+** with `pdo_mysql`
- Composer **2.x**
- Node.js **18+** and npm
- A running **MySQL** server (defaults assume `127.0.0.1:3306`, user `root`, empty password)

---

## Setup

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env        # skip if .env already exists
php artisan key:generate    # only if APP_KEY is empty

# 3. Create the database (the mysql CLI is optional — any method works)
#    The app expects a database named `laraclaude_news`.
php -r '(new PDO("mysql:host=127.0.0.1;port=3306","root",""))
        ->exec("CREATE DATABASE IF NOT EXISTS laraclaude_news
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");'

# 4. Migrate and seed 55 sample articles
php artisan migrate --seed

# 5. Build (or watch) the frontend
npm run dev        # Vite dev server with HMR  (keep running)
# or:
npm run build      # production build into public/build

# 6. Serve the app
php artisan serve  # http://127.0.0.1:8000
```

Open **http://127.0.0.1:8000**.

> During development run `npm run dev` and `php artisan serve` in two terminals.
> For a production-style run, `npm run build` once, then just `php artisan serve`.

### Database configuration

Set in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laraclaude_news
DB_USERNAME=root
DB_PASSWORD=
```

### Frontend API base URL

The Vue app reads the API base URL from a Vite env var (defaults to `/api`):

```env
VITE_API_BASE_URL=/api
```

---

## Running publicly

The site is reachable at **https://f4x.pp.ua** through Cloudflare. Request flow:

```
browser → Cloudflare → white IP :80 (router port-forward) → host :8000 (artisan serve)
```

Two helper scripts manage this:

```bash
./start.sh          # start the site (prompts for sudo only if MySQL is down)
./stop.sh           # stop artisan serve
./stop.sh --mysql   # stop artisan serve AND MySQL
```

`start.sh` does everything needed for a public run:

1. **Checks MySQL** on `127.0.0.1:3306`; if down, starts LAMPP
   (`sudo /opt/lampp/lampp startmysql`) and waits for the port.
2. **Removes `public/hot`** (Vite dev mode) and runs `npm run build` if no
   production build exists — so `@vite` serves hashed assets, not dev-server URLs.
3. **Starts `php artisan serve --host=0.0.0.0 --port=8000`** detached
   (`setsid`), writing the PID to `storage/serve.pid` and logs to
   `storage/logs/serve.log`. Binding to `0.0.0.0` (not `127.0.0.1`) is what
   makes the app reachable from the router/LAN.

### Cloudflare / network requirements

- **DNS** `f4x.pp.ua` → proxied (orange cloud) record pointing at the white IP.
- **SSL/TLS mode must be `Flexible`** (dashboard → SSL/TLS → Overview). Only
  port 80 is forwarded, so Cloudflare must reach the origin over HTTP — `Full`
  / `Full (strict)` would try port 443 and return **error 521**.
- **Router**: forward external port 80 → this host's port 8000.
- `.env` for the public run uses `APP_ENV=production` and
  `APP_URL=https://f4x.pp.ua`.

> `php artisan serve` is a single-threaded dev server and does not survive a
> reboot — re-run `./start.sh` after restarting the machine. For a hardened
> setup, serve `public/` via Apache/nginx and add a systemd unit.

---

## API Reference

| Method | Endpoint           | Description                                            |
|--------|--------------------|--------------------------------------------------------|
| GET    | `/api/news`        | Paginated articles (10/page), newest first             |
| GET    | `/api/news?page=2` | Specific page                                          |
| GET    | `/api/news/{slug}` | Single article by slug (`404` JSON if not found)       |

`/api/news` returns Laravel's standard paginator payload:

```json
{
  "current_page": 1,
  "data": [ { "id": 1, "title": "…", "slug": "…", "excerpt": "…",
              "content": "…", "image_url": null, "author": "…",
              "published_at": "2026-06-27T20:30:00.000000Z" } ],
  "last_page": 6,
  "per_page": 10,
  "total": 55
}
```

---

## Project Structure

```
app/
  Http/Controllers/Api/NewsController.php   # index() + show()
  Models/News.php                           # News model (table: news)
database/
  migrations/*_create_news_table.php
  seeders/NewsSeeder.php                    # 55 generated articles
  seeders/DatabaseSeeder.php
routes/
  api.php                                   # /api/news, /api/news/{slug}
  web.php                                   # catch-all -> SPA shell
resources/
  views/app.blade.php                       # SPA HTML shell (data-bs-theme="dark")
  css/app.scss                              # Bootstrap import + dark theme
  js/
    app.js                                  # bootstraps Vue
    router.js                               # Vue Router routes
    api.js                                  # axios instance
    App.vue                                 # layout + page transitions
    components/  AppHeader AppFooter NewsCard ContactForm
    views/       Home About Contacts NotFound
```

---

## Pages

- **`/`** — Homepage: latest 10 articles, "Load More" appends the next 10 via the API.
- **`/about`** — About F4X: mission/vision panels and a timeline.
- **`/contacts`** — Contact F4X: validated contact form (simulated submit) + contact info.
- **`*`** — 404 page with a link home.

---

## Useful Commands

```bash
php artisan migrate:fresh --seed   # rebuild DB with fresh sample data
npm run build                      # production asset build
php artisan route:list             # inspect routes
```
