<?php

declare(strict_types=1);

namespace App\Http\Requests\Equipments;

use App\Enums\EquipmentStatus;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class UpdateEquipmentRequest extends BaseFormRequest
{
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
            'status' => ['sometimes', Rule::enum(EquipmentStatus::class)],
            'image'  => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'name'          => 'name',
            'serial_number' => 'serial number',
            'type'          => 'type',
            'status'        => 'status',
            'image'         => 'image',
        ]);
    }
}
