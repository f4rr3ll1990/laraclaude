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
 * as the prompt instead.
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

    public function __construct(public News $article) {}

    public function handle(GeminiPromptService $prompts, PuterImageService $puter): void
    {
        // Derive an image prompt from the article body; fall back to the title
        // when Gemini can't produce one.
        $prompt = $prompts->generate($this->article->content) ?: $this->article->title;

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

        $this->article->update([
            'image_url' => Storage::disk('public')->url($path),
        ]);
    }
}
