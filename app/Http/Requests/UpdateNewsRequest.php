<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsRequest extends FormRequest
{
    /**
     * The route is protected by `auth:sanctum` + `admin`, so any request that
     * reaches this point is an authenticated admin.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for editing an article.
     *
     * Mirrors StoreNewsRequest. `slug` is intentionally absent — it stays
     * stable across edits so existing article links never break. The cover
     * image is machine-generated, so a client-supplied `image_url` is ignored
     * by the controller; regeneration goes through the dedicated endpoint.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:160'],
            'content' => ['required', 'string'],
            'author' => ['required', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
