<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

/**
 * Generates a cover image from a text prompt (an article title) via Puter's
 * free image API (`puter.ai.txt2img`, default model `gpt-image-2`).
 *
 * Puter's SDK is browser/Node-only, so this shells out to a small Node bridge
 * (`ptr_img_gen/generate.js`) authenticated with a saved auth token. The token
 * is obtained once interactively via `node ptr_img_gen/auth.js` and stored as
 * `PUTER_AUTH_TOKEN`. Returns the raw decoded image bytes, or null on failure.
 */
class PuterImageService
{
    /**
     * Generate an image for the given prompt.
     *
     * @return array{data: string, mime: string}|null  Decoded image bytes and
     *         MIME type, or null when generation is unavailable or fails.
     */
    public function generate(string $prompt): ?array
    {
        $token = config('services.puter.token');

        if (empty($token)) {
            Log::warning('Puter image generation skipped: PUTER_AUTH_TOKEN is not set. Run `node ptr_img_gen/auth.js`.');

            return null;
        }

        $result = Process::timeout(120)
            ->path(config('services.puter.script_dir'))
            ->env(['PUTER_AUTH_TOKEN' => $token])
            ->run([
                config('services.puter.node', 'node'),
                'generate.js',
                $prompt,
                config('services.puter.model', 'gpt-image-2'),
                config('services.puter.quality', 'low'),
            ]);

        if ($result->failed()) {
            Log::warning('Puter image generation process failed.', [
                'exit_code' => $result->exitCode(),
                'stderr' => $result->errorOutput(),
            ]);

            return null;
        }

        $payload = json_decode(trim($result->output()), true);

        if (empty($payload['data'])) {
            Log::warning('Puter image generation returned no image data.', [
                'output' => $result->output(),
                'stderr' => $result->errorOutput(),
            ]);

            return null;
        }

        return [
            'data' => base64_decode($payload['data']),
            'mime' => $payload['mime'] ?? 'image/png',
        ];
    }
}
