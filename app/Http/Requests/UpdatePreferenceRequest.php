<?php

namespace App\Http\Requests;

use App\Models\NewsSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePreferenceRequest extends FormRequest
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
        $sourceIds = NewsSource::where('is_active', true)->pluck('id')->toArray();
        return [
            'preferred_sources' => 'sometimes|array',
            'preferred_sources.*' => ['integer', Rule::in($sourceIds)],

            'preferred_categories' => 'sometimes|array',
            'preferred_categories.*' => 'string|max:100',

            'preferred_authors' => 'sometimes|array',
            'preferred_authors.*' => 'string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'preferred_sources.*.in' => 'The selected source is invalid or not active.',
            'preferred_categories.*.string' => 'Each category must be a string.',
            'preferred_authors.*.string' => 'Each author must be a string.',
        ];
    }
}
