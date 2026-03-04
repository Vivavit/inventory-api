<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => 'integer|min:1|max:5',
            'title' => 'string|max:255',
            'comment' => 'nullable|string',
        ];
    }
}
