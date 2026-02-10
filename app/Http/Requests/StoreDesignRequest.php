<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDesignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:140'],
            'canvas_json' => ['required', 'array'],
            'meta' => ['nullable', 'array'],
            'status' => ['nullable', 'in:draft,published,archived'],
        ];
    }
}
