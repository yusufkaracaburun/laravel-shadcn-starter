<?php

declare(strict_types=1);

namespace App\Http\Requests\Vehicles;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class AssignDriversRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'drivers' => ['required', 'array', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'drivers' => 'drivers',
        ]);
    }
}
