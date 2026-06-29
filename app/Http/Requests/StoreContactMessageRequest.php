<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    /**
     * This is a public endpoint — anyone may submit the contact form.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for a contact-form submission.
     *
     * Mirrors the client-side checks in ContactForm.vue (name/email/subject
     * required, message at least 10 chars) so both layers agree.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }
}
