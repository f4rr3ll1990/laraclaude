<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;

class ContactMessageController extends Controller
{
    /**
     * Store a contact-form submission. Public endpoint (see routes/api.php).
     *
     * Validation lives in StoreContactMessageRequest and mirrors the
     * client-side rules in ContactForm.vue. Returns 201 on success.
     */
    public function store(StoreContactMessageRequest $request): JsonResponse
    {
        $message = ContactMessage::create($request->validated());

        return response()->json(['data' => $message], 201);
    }
}
