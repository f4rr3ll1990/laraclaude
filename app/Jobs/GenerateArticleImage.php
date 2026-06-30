<?php

namespace App\Jobs;

use App\Models\News;
use App\Services\GeminiPromptService;
use App\Services\PuterImageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

/**
 * Generates a cover image for a freshly created article and stores the result
 * on the public disk, then writes the URL back to `image_url`.
 *
 * The image prompt is derived from the article body via Gemini
 * (GeminiPromptService) so the cover reflects the whole story; the prompt is
 * then rendered by Puter. If Gemini is unavailable the article title is used
 * as the prompt instead. A caller may also pass an explicit `$promptOverride`
 * (e.g. an admin-edited prompt) to skip Gemini and render it verbatim.
 *
 * Dispatched from NewsController@store. On failure the article simply keeps a
 * null `image_url` (the frontend renders a gradient placeholder for those).
 */
class GenerateArticleImage implements ShouldQueue
{
    use Queueable;

    /** Retry transient Puter outages a couple of times before giving up. */
    public int $tries = 3;

    public int $backoff = 10;

    /**
     * @param  string|null  $promptOverride  When non-empty, rendered as-is
     *                                        instead of asking Gemini.
     */
    public function __construct(public News $article, public ?string $promptOverride = null) {}

    public function handle(GeminiPromptService $prompts, PuterImageService $puter): void
    {
        // Use the caller-supplied prompt when given; otherwise derive one from
        // the article body via Gemini, falling back to the title.
        $override = $this->promptOverride !== null ? trim($this->promptOverride) : '';

        $prompt = $override !== ''
            ? $override
            : ($prompts->generate($this->article->content) ?: $this->article->title);

        $image = $puter->generate($prompt);

        if (! $image) {
            return; // Graceful: leave image_url null.
        }

        $ext = match (true) {
            str_contains($image['mime'], 'jpeg') => 'jpg',
            str_contains($image['mime'], 'png') => 'png',
            str_contains($image['mime'], 'webp') => 'webp',
            default => 'img',
        };

        $path = "articles/{$this->article->id}.{$ext}";

        Storage::disk('public')->put($path, $image['data']);

        // The file path is stable per article, so append a cache-busting query
        // param — otherwise browsers (and the CDN) keep serving the previous
        // cover after a regeneration. parse_url(PHP_URL_PATH) in destroy()
        // ignores the query, so cleanup still works.
        $url = Storage::disk('public')->url($path).'?v='.now()->timestamp;

        $this->article->update([
            'image_url' => $url,
        ]);
    }
}
