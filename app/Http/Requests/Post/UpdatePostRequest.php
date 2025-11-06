<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Biarkan selalu true; otorisasi author ditangani via $this->authorize('update', $post) di controller
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => ['sometimes', 'string', 'max:255'],
            'content'      => ['sometimes', 'string'],
            'is_draft'     => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
