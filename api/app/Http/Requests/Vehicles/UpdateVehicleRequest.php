<?php

declare(strict_types=1);

namespace App\Http\Requests\Vehicles;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateVehicleRequest extends FormRequest
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
        return [
            'make' => ['sometimes', 'string', 'max:255'],
            'model' => ['sometimes', 'string', 'max:255'],
            'year' => ['sometimes', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'license_plate' => [
                'sometimes', 'string', 'max:20',
                Rule::unique('vehicles', 'license_plate')->ignore($this->vehicle),
            ],
            'vin' => [
                'nullable', 'string', 'max:255',
                Rule::unique('vehicles', 'vin')->ignore($this->vehicle),
            ],
            'status' => ['sometimes', 'string', 'in:active,maintenance,inactive'],
            'drivers' => ['sometimes', 'array'],
            'drivers.*' => ['exists:users,id'],
        ];
    }
}
