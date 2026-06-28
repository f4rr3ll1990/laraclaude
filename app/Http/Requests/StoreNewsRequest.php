<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsRequest extends FormRequest
{
    /**
     * The route is already protected by the `auth:sanctum` middleware, so any
     * request that reaches this point is authenticated and authorised.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a news article.
     *
     * `slug` is intentionally absent — it is derived from the title in the
     * controller. `excerpt` is optional; when omitted it is generated from the
     * content. The `excerpt` cap mirrors the `string('excerpt', 160)` column.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:160'],
            'content' => ['required', 'string'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'author' => ['required', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
