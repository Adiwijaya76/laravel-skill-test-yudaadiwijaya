<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        // JANGAN cek auth di sini: biarkan route middleware 'auth' yang handle (agar guest => 302)
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['required', 'string'],
            'is_draft'     => ['required', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
