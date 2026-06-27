<?php

use Illuminate\Support\Facades\Route;

// Serve the Vue single-page application for every non-API route so that
// deep links and page refreshes are handled by Vue Router on the client.
Route::view('/{any?}', 'app')->where('any', '^(?!api).*$');
