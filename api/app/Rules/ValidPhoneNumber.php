<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation rule for phone numbers.
 *
 * Validates phone numbers using the brick/phonenumber library.
 * Defaults to Netherlands (NL) region if not specified in config.
 *
 * @example
 * // In a Form Request
 * public function rules(): array
 * {
 *     return [
 *         'phone' => ['required', new ValidPhoneNumber()],
 *     ];
 * }
 * @example
 * // In a Controller
 * $request->validate([
 *     'phone' => ['required', 'string', new ValidPhoneNumber()],
 * ]);
 * @example
 * // Using Validator facade
 * Validator::make($data, [
 *     'phone' => [new ValidPhoneNumber()],
 * ])->validate();
 */
final class ValidPhoneNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $region = config('app.phone_default_region', 'NL');
            $number = PhoneNumber::parse($value, $region);

            if (!$number->isValidNumber()) {
                $fail('Vul een geldig Nederlands telefoonnummer in.', null);
            }
        } catch (PhoneNumberParseException) {
            $fail('Vul een geldig Nederlands telefoonnummer in.', null);
        }
    }
}
