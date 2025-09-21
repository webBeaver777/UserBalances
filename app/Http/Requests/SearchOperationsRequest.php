<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchOperationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'sort_direction' => 'nullable|in:asc,desc',
            'type' => 'nullable|in:deposit,withdrawal',
            'status' => 'nullable|in:pending,completed,failed',
        ];
    }

    public function messages(): array
    {
        return [
            'search.string' => 'Поисковый запрос должен быть строкой',
            'search.max' => 'Поисковый запрос не должен превышать 255 символов',
            'limit.integer' => 'Лимит должен быть числом',
            'limit.min' => 'Лимит должен быть больше 0',
            'limit.max' => 'Лимит не должен превышать 100',
            'page.integer' => 'Номер страницы должен быть числом',
            'page.min' => 'Номер страницы должен быть больше 0',
            'sort_direction.in' => 'Направление сортировки должно быть asc или desc',
            'type.in' => 'Тип операции должен быть deposit или withdrawal',
            'status.in' => 'Статус должен быть pending, completed или failed',
        ];
    }
}
