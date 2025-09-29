<?php

namespace App\Http\Requests;

use App\Models\NewsSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleSearchRequest extends FormRequest
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
            'search' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:500', // Increased for multiple categories
            'source' => 'sometimes|array',
            'source.*' => ['integer', Rule::in($sourceIds)],
            'author' => 'sometimes|string|max:255',
            'from_date' => 'sometimes|date|before_or_equal:to_date',
            'to_date' => 'sometimes|date|after_or_equal:from_date',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'source.*.in' => 'The selected source is invalid or not active.',
            'from_date.before_or_equal' => 'The from date must be before or equal to the to date.',
            'to_date.after_or_equal' => 'The to date must be after or equal to the from date.',
        ];
    }
}
