<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * Single article by slug. Returns 404 JSON when not found.
     */
    public function show(string $slug): JsonResponse
    {
        $article = News::where('slug', $slug)->firstOrFail();

        return response()->json(['data' => $article]);
    }
}
