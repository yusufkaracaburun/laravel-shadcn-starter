<?php

declare(strict_types=1);

namespace App\Http\Requests\Vehicles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class StoreVehicleRequest extends FormRequest
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
            'make'          => ['required', 'string', 'max:255'],
            'model'         => ['required', 'string', 'max:255'],
            'year'          => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'license_plate' => ['required', 'string', 'max:20', 'unique:vehicles,license_plate'],
            'vin'           => ['nullable', 'string', 'max:255', 'unique:vehicles,vin'],
            'status'        => ['sometimes', 'string', 'in:active,maintenance,inactive'],
            'drivers'       => ['sometimes', 'array'],
            'drivers.*'     => ['exists:users,id'],
        ];
    }
}
