<?php

declare(strict_types=1);

namespace App\Http\Requests\Vehicles;

use App\Enums\VehicleStatus;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class StoreVehicleRequest extends BaseFormRequest
{
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
            'status'        => ['sometimes', Rule::enum(VehicleStatus::class)],
            'drivers'       => ['sometimes', 'array'],
            'drivers.*'     => ['exists:users,id'],
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
