<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'design_id' => ['nullable', 'integer', 'exists:designs,id'],
            'asset' => ['required', 'file', 'mimes:png,jpg,jpeg,svg', 'max:10240'],
        ];
    }
}
