<?php

namespace Modules\School\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('teacher');
    }

    public function rules(): array
    {
        $maxPoints = $this->route('submission')->assignment->max_points;

        return [
            'grade' => ['required', 'integer', 'min:0', 'max:'.$maxPoints],
            'feedback' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'grade.required' => 'Grade is required.',
            'grade.min' => 'Grade cannot be negative.',
            'grade.max' => 'Grade cannot exceed the assignment maximum points.',
        ];
    }
}
