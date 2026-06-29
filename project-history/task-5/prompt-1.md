# Plan: Auto-generate article images via Gemini 2.5 Flash Image on POST /api/news
## Context
POST /api/news already exists (auth:sanctum-protected → NewsController@store), and the image_url column is currently always null (seeder sets null; the frontend renders a gradient placeholder for null images — NewsCard.vue:37, ArticleDetail.vue:134).

We want every newly created article to get an AI-generated cover image. On article creation, an image is generated with Gemini 2.5 Flash Image using the article title as the prompt, then stored and linked via image_url.

### Decisions (confirmed with user):

Queued background job — store() saves the article immediately with image_url = null and dispatches a job that generates the image and updates the row afterward. Queue infra is ready: QUEUE_CONNECTION=database and the jobs table migration already exists (database/migrations/0001_01_01_000002_create_jobs_table.php). A worker must be running.
Graceful failure — if Gemini fails (error/timeout/quota), log it and leave image_url = null. The article stays fully usable thanks to the existing placeholder fallback.
## Gemini API reference (verified via current docs)
Endpoint: POST https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image:generateContent
Headers: x-goog-api-key: <GEMINI_API_KEY>, Content-Type: application/json
Body: { "contents": [ { "parts": [ { "text": "<title>" } ] } ] }
Response: image bytes are base64 in candidates[0].content.parts[].inlineData.data, with inlineData.mimeType (e.g. image/png). Iterate parts — a part may also contain text.
## Changes
### 1. Config — config/services.php
Add a gemini block (no existing entry):

'gemini' => [
    'key' => env('GEMINI_API_KEY'),
    'image_model' => env('GEMINI_IMAGE_MODEL', 'gemini-2.5-flash-image'),
],
Add GEMINI_API_KEY= to .env.example (the real key is already in production .env).

### 2. New service — app/Services/GeminiImageService.php
Single generate(string $prompt): ?array method. Reuses Laravel's built-in HTTP client (Illuminate\Support\Facades\Http — no Guzzle dependency to add).

POST the generateContent request with Http::timeout(60)->withHeaders(['x-goog-api-key' => $key]).
If config('services.gemini.key') is empty or $response->failed() → Log::warning(...), return null.
Walk data_get($json, 'candidates.0.content.parts', []), find the first part with inlineData.data, return ['data' => base64_decode(...), 'mime' => inlineData.mimeType ?? 'image/png'].
No image part found → return null.
### 3. New job — app/Jobs/GenerateArticleImage.php
implements ShouldQueue, constructor takes the News $article.

public function handle(GeminiImageService $gemini): void
{
    $image = $gemini->generate($this->article->title);
    if (! $image) {
        return; // graceful: leave image_url null
    }
    $ext  = str_contains($image['mime'], 'jpeg') ? 'jpg' : (str_contains($image['mime'], 'png') ? 'png' : 'img');
    $path = "articles/{$this->article->id}.{$ext}";
    Storage::disk('public')->put($path, $image['data']);
    $this->article->update(['image_url' => Storage::disk('public')->url($path)]);
}
Files land in storage/app/public/articles/{id}.{ext}; Storage::disk('public')->url() yields {APP_URL}/storage/articles/{id}.ext — the absolute URL the frontend uses directly in <img :src>.
Set public int $tries = 3; so transient Gemini outages get retried; the graceful return null path simply ends without an image when generation genuinely can't produce one.
### 4. Controller — app/Http/Controllers/Api/NewsController.php (store)
After $article = News::create($data);:

Drop any client-supplied image_url before create (unset($data['image_url'])) so the row starts null and is owned by the generator. (Optionally also remove image_url from StoreNewsRequest::rules() since it's now machine-generated — keeping it is harmless.)
GenerateArticleImage::dispatch($article);
Keep returning 201 with the article (image_url null at this instant; populated once the worker runs).
### 5. Storage & worker (ops, not code)
php artisan storage:link — the public/storage symlink does not exist yet; without it the generated URLs 404.
A queue worker must process the database queue: php artisan queue:work. Document this and add it to start.sh (background, alongside artisan serve) so production picks up dispatched jobs.
Files
Path	Change
config/services.php	add gemini config block
.env.example	add GEMINI_API_KEY= placeholder
app/Services/GeminiImageService.php	new — Gemini generateContent call + base64 decode
app/Jobs/GenerateArticleImage.php	new — queued image gen + storage + image_url update
app/Http/Controllers/Api/NewsController.php	dispatch job in store(), unset client image_url
start.sh	launch php artisan queue:work alongside serve
## Verification (end-to-end)
php artisan storage:link (once), php artisan migrate (jobs table), then start a worker: php artisan queue:work --once (or leave queue:work running).
Get a token: POST /api/register (or /api/login) → copy the Bearer token.
Create an article:
curl -X POST http://127.0.0.1:8000/api/news \
  -H "Authorization: Bearer <token>" -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"title":"Quantum breakthrough reshapes computing","content":"...","author":"Desk"}'
Expect 201 with data.image_url: null.
Run/observe the worker; confirm a file appears at storage/app/public/articles/{id}.png and a row in failed_jobs does not (on success).
GET /api/news/{slug} → image_url now a …/storage/articles/{id}.png URL; open it in a browser to confirm the image renders, and load the SPA to see the real cover replace the placeholder.
Failure path: temporarily set a bad GEMINI_API_KEY, create an article → article still created 201, worker logs a warning, image_url stays null, frontend shows the gradient placeholder.