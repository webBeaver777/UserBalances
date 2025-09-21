<?php

namespace App\Http\Requests;

use App\Models\Operation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:' . implode(',', [
                Operation::STATUS_PENDING,
                Operation::STATUS_COMPLETED,
                Operation::STATUS_FAILED
            ]),
            'description' => 'sometimes|string|max:255|min:3',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Статус должен быть одним из: pending, completed, failed',
            'description.string' => 'Описание должно быть строкой',
            'description.max' => 'Описание не должно превышать 255 символов',
            'description.min' => 'Описание должно содержать минимум 3 символа',
        ];
    }
}
