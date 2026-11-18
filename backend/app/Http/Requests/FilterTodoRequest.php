<?php

namespace App\Http\Requests;

use App\Enums\TodoPriority;
use App\Enums\TodoStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterTodoRequest extends FormRequest
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
        foreach (['assignee', 'status', 'priority'] as $field) {
            if ($this->filled($field)) {
                $this->merge([$field => $this->splitList($this->input($field))]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'assignee' => ['nullable', 'array'],
            'assignee.*' => ['string', 'max:255'],
            'start' => ['nullable', 'date_format:Y-m-d'],
            'end' => ['nullable', 'date_format:Y-m-d', Rule::when($this->filled('start'), 'after_or_equal:start')],
            'min' => ['nullable', 'numeric', 'min:0'],
            'max' => ['nullable', 'numeric', 'min:0', Rule::when($this->filled('min'), 'gte:min')],
            'status' => ['nullable', 'array'],
            'status.*' => [Rule::enum(TodoStatus::class)],
            'priority' => ['nullable', 'array'],
            'priority.*' => [Rule::enum(TodoPriority::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'end.after_or_equal' => 'The end date must be after or equal to the start date.',
            'max.gte' => 'The max value must be greater than or equal to the min value.',
            'status.*.enum' => 'Each status must be one of: '.implode(', ', TodoStatus::values()).'.',
            'priority.*.enum' => 'Each priority must be one of: '.implode(', ', TodoPriority::values()).'.',
        ];
    }

    private function splitList(mixed $value): array
    {
        $items = is_array($value) ? $value : explode(',', (string) $value);

        return collect($items)
            ->map(fn ($item) => is_scalar($item) ? trim((string) $item) : '')
            ->filter(fn (string $item) => $item !== '')
            ->values()
            ->all();
    }
}