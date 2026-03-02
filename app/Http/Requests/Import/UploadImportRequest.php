<?php

namespace App\Http\Requests\Import;

use Illuminate\Foundation\Http\FormRequest;

class UploadImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'], // 10MB
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'The file must be a CSV file.',
            'file.max' => 'The file must not be larger than 10MB.',
        ];
    }
}
