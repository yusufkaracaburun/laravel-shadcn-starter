<?php

declare(strict_types=1);

namespace App\Http\Requests\Equipments;

use App\Enums\EquipmentStatus;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class StoreEquipmentRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'serial_number' => ['required', 'string', 'max:255', 'unique:equipments,serial_number'],
            'type'          => ['required', 'string', 'max:255'],
            'status'        => ['sometimes', Rule::enum(EquipmentStatus::class)],
            'image'         => ['nullable', 'string'],
        ];
    }
}
