<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDesignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:2', 'max:140'],
            'canvas_json' => ['sometimes', 'array'],
            'meta' => ['sometimes', 'array'],
            'status' => ['sometimes', 'in:draft,published,archived'],
            'change_note' => ['nullable', 'string', 'max:250'],
            'autosave' => ['sometimes', 'boolean'],
        ];
    }
}
