// Headless Puter image generator — invoked by the Laravel PuterImageService.
//
// Usage:   node generate.js "<prompt>" [model] [quality]
// Auth:    PUTER_AUTH_TOKEN env var (obtain once via `node auth.js`).
//
// On success writes a single JSON line to stdout:
//   {"mime":"image/png","data":"<base64>"}
// On failure writes a message to stderr and exits non-zero.
import { init } from '@heyputer/puter.js/src/init.cjs';

// Puter opens a background socket on init that can reject after we already have
// our image; swallow that noise so it never masks a successful result.
process.on('unhandledRejection', () => {});

const prompt = process.argv[2];
const model = process.argv[3] || process.env.PUTER_IMAGE_MODEL || 'gpt-image-2';
const quality = process.argv[4] || process.env.PUTER_IMAGE_QUALITY || 'low';
const token = process.env.PUTER_AUTH_TOKEN;

const fail = (msg, code) => {
    console.error(msg);
    process.exit(code);
};

if (!prompt) fail('Missing prompt (argv[2]).', 2);
if (!token) fail('Missing PUTER_AUTH_TOKEN.', 3);

try {
    const puter = init(token); // reuse the saved token — no browser
    const image = await puter.ai.txt2img(prompt, { model, quality });

    // txt2img resolves to an Image-like object whose `src` is a data URL.
    const src = typeof image === 'string' ? image : image?.src;
    const match = /^data:(image\/[\w.+-]+);base64,(.*)$/s.exec(src ?? '');

    if (!match) fail('Unexpected image response (no base64 data URL).', 4);

    // Exit only once the (potentially multi-MB) payload has been flushed to the
    // OS pipe. Calling process.exit() synchronously right after write() truncates
    // large async pipe writes, yielding invalid JSON on the PHP side.
    process.stdout.write(
        JSON.stringify({ mime: match[1], data: match[2] }),
        () => process.exit(0),
    );
} catch (err) {
    fail(err?.message || err?.error?.message || String(err), 1);
}
