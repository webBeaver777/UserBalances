<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Авторизация проверяется через middleware auth:sanctum
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'description' => 'required|string|max:255|min:3',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Тип операции обязателен',
            'type.in' => 'Тип операции должен быть deposit или withdrawal',
            'amount.required' => 'Сумма операции обязательна',
            'amount.numeric' => 'Сумма должна быть числом',
            'amount.min' => 'Минимальная сумма операции 0.01',
            'amount.max' => 'Максимальная сумма операции 999,999.99',
            'description.required' => 'Описание операции обязательно',
            'description.string' => 'Описание должно быть строкой',
            'description.max' => 'Описание не должно превышать 255 символов',
            'description.min' => 'Описание должно содержать минимум 3 символа',
        ];
    }
}
