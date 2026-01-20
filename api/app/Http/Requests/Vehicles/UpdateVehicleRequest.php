<?php

declare(strict_types=1);

namespace App\Http\Requests\Vehicles;

use App\Enums\VehicleStatus;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class UpdateVehicleRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'make'          => ['sometimes', 'string', 'max:255'],
            'model'         => ['sometimes', 'string', 'max:255'],
            'year'          => ['sometimes', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'license_plate' => [
                'sometimes', 'string', 'max:20',
                Rule::unique('vehicles', 'license_plate')->ignore($this->vehicle),
            ],
            'vin' => [
                'nullable', 'string', 'max:255',
                Rule::unique('vehicles', 'vin')->ignore($this->vehicle),
            ],
            'status'    => ['sometimes', Rule::enum(VehicleStatus::class)],
            'drivers'   => ['sometimes', 'array'],
            'drivers.*' => ['exists:users,id'],
        ];
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'make'          => 'make',
            'model'         => 'model',
            'year'          => 'year',
            'license_plate' => 'license plate',
            'vin'           => 'VIN',
            'status'        => 'status',
            'drivers'       => 'drivers',
        ]);
    }
}
