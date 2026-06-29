// One-time browser auth for Puter image generation.
//
// Run once interactively:  node auth.js
// It opens a browser, waits for you to confirm, then prints your auth token.
// Copy that token into the Laravel .env as PUTER_AUTH_TOKEN — every later
// generation reuses it (no browser needed) until it is revoked/expires.
import { getAuthToken } from '@heyputer/puter.js/src/init.cjs';

const token = await getAuthToken(); // opens browser, resolves once you confirm

// Print ONLY the token on stdout so it can be piped/copied cleanly.
console.log(token);
process.exit(0);
