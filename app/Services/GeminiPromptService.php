<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Turns an article's body text into a concise, vivid image-generation prompt
 * using a Gemini text model (`generateContent`, default `gemini-3.1-flash-lite`).
 *
 * The resulting prompt is fed to PuterImageService to render the cover image,
 * so the picture reflects the whole story rather than just the headline.
 * Reuses Laravel's HTTP client — no extra dependency.
 */
class GeminiPromptService
{
    /** Cap the article text sent to Gemini to keep token usage bounded. */
    private const MAX_CONTENT_CHARS = 4000;

    /**
     * Build an image-generation prompt describing the given article body.
     *
     * @return string|null  The prompt text, or null when Gemini is unavailable
     *                      or fails (caller should fall back to the title).
     */
    public function generate(string $content): ?string
    {
        $key = config('services.gemini.key');

        if (empty($key)) {
            Log::warning('Gemini prompt generation skipped: GEMINI_API_KEY is not set.');

            return null;
        }

        $article = trim(strip_tags($content));

        if ($article === '') {
            return null;
        }

        $article = mb_substr($article, 0, self::MAX_CONTENT_CHARS);

        $instruction = <<<'TXT'
        You write prompts for an AI image generator that creates cover images for
        news articles. Read the article below and output a SINGLE vivid,
        descriptive English prompt (one or two sentences) for a photorealistic
        editorial cover image that captures its subject and mood. Describe the
        scene, subjects, setting and lighting. Do not include any text, captions,
        watermarks, quotation marks, or commentary — output only the prompt.

        Article:
        TXT;

        $model = config('services.gemini.text_model', 'gemini-3.1-flash-lite');
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $response = Http::timeout(60)
            ->withHeaders(['x-goog-api-key' => $key])
            ->post($endpoint, [
                'contents' => [
                    ['parts' => [['text' => $instruction."\n\n".$article]]],
                ],
            ]);

        if ($response->failed()) {
            Log::warning('Gemini prompt generation request failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $prompt = '';

        foreach (data_get($response->json(), 'candidates.0.content.parts', []) as $part) {
            if (! empty($part['text'])) {
                $prompt .= $part['text'];
            }
        }

        $prompt = trim($prompt);

        if ($prompt === '') {
            Log::warning('Gemini prompt generation returned no text.', [
                'body' => $response->body(),
            ]);

            return null;
        }

        return $prompt;
    }
}
