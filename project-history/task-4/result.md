# What was added
## Authentication (Laravel Sanctum, token-based)

Installed Sanctum via php artisan install:api; the personal_access_tokens migration ran against MySQL.
User.php now uses the HasApiTokens trait.
AuthController.php: register (201 + token), login (token + user, generic 422 on bad creds so the email isn't leaked), logout (revokes the current token).
## Create-article endpoint

NewsController::store() — validates, auto-generates a unique slug, derives the excerpt from content when omitted, defaults published_at to now, returns 201 {data: ...}.
News::uniqueSlug() — transliterates the title (Cyrillic → latin, e.g. Заголовок новини → zagolovok-novini), appends -2, -3… on collision, with a random fallback for un-slug-able titles.
Form Requests: StoreNewsRequest, LoginRequest, RegisterRequest.
Routes (api.php) — public GET /news, GET /news/{slug}; public POST /register, POST /login; and behind auth:sanctum: POST /news, POST /logout, GET /user.

## Seeding & tests — DatabaseSeeder creates an idempotent admin (admin@f4x.test / password); 11 new feature tests in AuthTest and NewsCreationTest.

Verified live against php artisan serve: login → token; POST /news without a token → 401; with the Bearer token → 201 with the created article (Ukrainian payload, slug zagolovok-novini); logout → token revoked. Code passes Pint.

## Two notes:

For token (Bearer) auth, the EnsureFrontendRequestsAreStateful middleware the task mentions for the api group isn't needed — that's only for SPA cookie sessions. I used the auth:sanctum guard, which is the correct token path. Say the word if you also want stateful SPA cookie auth wired in.
I added the api_token requirement as Sanctum personal access tokens (the recommended approach) rather than a plaintext api_token column — that's what gives you multi-token issue/revoke.
Want me to record this task under project-history/task-4/?