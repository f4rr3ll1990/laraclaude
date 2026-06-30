<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Jobs\GenerateArticleImage;
use App\Models\News;
use App\Services\GeminiPromptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    /**
     * Paginated list of articles, newest first.
     * Respects ?page=X automatically. Optional ?per_page (1-50, default 10)
     * and ?exclude_slug (omit one article, used for "Related Articles").
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min($perPage, 50));

        $news = News::query()
            ->when(
                $request->query('exclude_slug'),
                fn ($query, $slug) => $query->where('slug', '!=', $slug)
            )
            ->orderByDesc('published_at')
            ->paginate($perPage);

        return response()->json($news);
    }

    /**
     * Create a new article. Protected by `auth:sanctum` (see routes/api.php).
     *
     * The slug is derived from the title; the excerpt falls back to a 150-char
     * summary of the content (matching NewsCard.vue) and `published_at`
     * defaults to now when omitted. The cover image is generated from the title
     * by a queued GenerateArticleImage job, so the article starts with a null
     * `image_url` that is filled in once the worker runs. Returns 201.
     */
    public function store(StoreNewsRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['slug'] = News::uniqueSlug($data['title']);

        if (empty($data['excerpt'])) {
            $data['excerpt'] = Str::limit(strip_tags($data['content']), 150);
        }

        $data['published_at'] = $data['published_at'] ?? now();

        // The cover image is machine-generated; ignore any client-supplied URL
        // so the row starts null and is owned by GenerateArticleImage.
        unset($data['image_url']);

        $article = News::create($data);

        GenerateArticleImage::dispatch($article);

        return response()->json(['data' => $article], 201);
    }

    /**
     * Single article by slug. Returns 404 JSON when not found.
     */
    public function show(string $slug): JsonResponse
    {
        $article = News::where('slug', $slug)->firstOrFail();

        return response()->json(['data' => $article]);
    }

    /**
     * Update an existing article. Protected by `auth:sanctum` + `admin`.
     *
     * The `slug` is intentionally left untouched so existing links keep
     * working even when the title changes. The excerpt falls back to a
     * 150-char summary of the content when cleared. The cover image is not
     * touched here — use regenerateImage() to rebuild it. Resolved by slug via
     * implicit route-model binding (see News::getRouteKeyName).
     */
    public function update(UpdateNewsRequest $request, News $news): JsonResponse
    {
        $data = $request->validated();

        if (empty($data['excerpt'])) {
            $data['excerpt'] = Str::limit(strip_tags($data['content']), 150);
        }

        $data['published_at'] = $data['published_at'] ?? $news->published_at ?? now();

        $news->update($data);

        return response()->json(['data' => $news]);
    }

    /**
     * Delete an article and its generated cover image. Admin only.
     */
    public function destroy(News $news): JsonResponse
    {
        // Remove the machine-generated cover (stored at articles/{id}.{ext})
        // so deleting an article doesn't orphan its image on the public disk.
        if ($news->image_url) {
            $path = ltrim(parse_url($news->image_url, PHP_URL_PATH) ?? '', '/');
            $path = Str::after($path, 'storage/');
            if ($path !== '') {
                Storage::disk('public')->delete($path);
            }
        }

        $news->delete();

        return response()->json(null, 204);
    }

    /**
     * Build (but do not render) an image prompt for an article via Gemini.
     * Admin only. Lets the admin preview/edit the prompt before generating.
     *
     * Returns the Gemini-produced prompt, or — when Gemini is unavailable —
     * the article title as a fallback, with a `source` flag so the UI can say
     * where it came from. Runs synchronously (no queue).
     */
    public function generatePrompt(News $news, GeminiPromptService $prompts): JsonResponse
    {
        $prompt = $prompts->generate($news->content);

        return response()->json(['data' => [
            'prompt' => $prompt ?: $news->title,
            'source' => $prompt ? 'gemini' : 'title',
        ]]);
    }

    /**
     * Re-dispatch cover-image generation for an article. Admin only.
     *
     * Resets `image_url` to null immediately (the frontend placeholder covers
     * the gap) and queues GenerateArticleImage to render a fresh cover. An
     * optional `prompt` in the body is rendered verbatim (skipping Gemini),
     * letting an admin supply a hand-edited prompt.
     */
    public function regenerateImage(Request $request, News $news): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['nullable', 'string', 'max:2000'],
        ]);

        $news->update(['image_url' => null]);

        $prompt = isset($validated['prompt']) && trim($validated['prompt']) !== ''
            ? $validated['prompt']
            : null;

        GenerateArticleImage::dispatch($news, $prompt);

        return response()->json(['data' => $news]);
    }
}
