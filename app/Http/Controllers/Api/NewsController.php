<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * defaults to now when omitted. Returns 201 with the created article.
     */
    public function store(StoreNewsRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['slug'] = News::uniqueSlug($data['title']);

        if (empty($data['excerpt'])) {
            $data['excerpt'] = Str::limit(strip_tags($data['content']), 150);
        }

        $data['published_at'] = $data['published_at'] ?? now();

        $article = News::create($data);

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
}
