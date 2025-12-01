<?php

namespace Modules\School\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('school.submit_assignments');
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Submission content is required.',
            'content.min' => 'Submission must be at least 10 characters.',
        ];
    }
}
