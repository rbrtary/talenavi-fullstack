<?php

namespace App\Http\Requests;

use App\Enums\TodoPriority;
use App\Enums\TodoStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTodoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status') ?? TodoStatus::Pending->value,
            'time_tracked' => $this->input('time_tracked') ?? 0,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'assignee' => ['nullable', 'string', 'max:255'],
            'due_date' => ['bail', 'required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time_tracked' => ['numeric', 'min:0'],
            'status' => [Rule::enum(TodoStatus::class)],
            'priority' => ['required', Rule::enum(TodoPriority::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'due_date.after_or_equal' => 'The due date cannot be in the past.',
            'status.enum' => 'The status must be one of: '.implode(', ', TodoStatus::values()).'.',
            'priority.enum' => 'The priority must be one of: '.implode(', ', TodoPriority::values()).'.',
        ];
    }
}