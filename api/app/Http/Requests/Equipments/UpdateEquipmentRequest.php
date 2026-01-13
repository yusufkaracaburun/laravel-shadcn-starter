<?php

declare(strict_types=1);

namespace App\Http\Requests\Equipments;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class UpdateEquipmentRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'          => ['sometimes', 'string', 'max:255'],
            'serial_number' => [
                'sometimes', 'string', 'max:255',
                Rule::unique('equipments', 'serial_number')->ignore($this->equipment),
            ],
            'type'   => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:active,maintenance,inactive'],
            'image'  => ['nullable', 'string'],
        ];
    }
}
