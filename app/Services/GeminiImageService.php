<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the Gemini "generateContent" image API.
 *
 * Generates a cover image from a text prompt (an article title) using the
 * `gemini-2.5-flash-image` model and returns the raw decoded bytes. Reuses
 * Laravel's built-in HTTP client — no extra dependency.
 */
class GeminiImageService
{
    /**
     * Generate an image for the given prompt.
     *
     * @return array{data: string, mime: string}|null  Decoded image bytes and
     *         MIME type, or null when generation is unavailable or fails.
     */
    public function generate(string $prompt): ?array
    {
        $key = config('services.gemini.key');

        if (empty($key)) {
            Log::warning('Gemini image generation skipped: GEMINI_API_KEY is not set.');

            return null;
        }

        $model = config('services.gemini.image_model', 'gemini-2.5-flash-image');
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $response = Http::timeout(60)
            ->withHeaders(['x-goog-api-key' => $key])
            ->post($endpoint, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]);

        if ($response->failed()) {
            Log::warning('Gemini image generation request failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        // Image bytes arrive base64-encoded in an `inlineData` part. A response
        // may also contain a text part, so scan for the first image part.
        foreach (data_get($response->json(), 'candidates.0.content.parts', []) as $part) {
            if (! empty($part['inlineData']['data'])) {
                return [
                    'data' => base64_decode($part['inlineData']['data']),
                    'mime' => $part['inlineData']['mimeType'] ?? 'image/png',
                ];
            }
        }

        Log::warning('Gemini image generation returned no image data.', [
            'body' => $response->body(),
        ]);

        return null;
    }
}
