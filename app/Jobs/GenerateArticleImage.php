<?php

namespace App\Jobs;

use App\Models\News;
use App\Services\PuterImageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

/**
 * Generates a cover image for a freshly created article via Puter and stores
 * the result on the public disk, then writes the URL back to `image_url`.
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

    public function handle(PuterImageService $puter): void
    {
        $image = $puter->generate($this->article->title);

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
