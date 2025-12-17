<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Address formatting helper class.
 *
 * Provides methods for formatting addresses in different styles.
 */
final class AddressHelper
{
    /**
     * Format an address in different styles.
     *
     * @param  string|null  $address  Street address
     * @param  string|null  $zipcode  Postal code
     * @param  string|null  $city  City name
     * @return array{short: string, inline: string, multiline: string, full: string}
     */
    public static function formatAddress(?string $address, ?string $zipcode, ?string $city): array
    {
        return [
            'short'     => self::shortAddress($address, $zipcode, $city),
            'inline'    => self::inlineAddress($address, $zipcode, $city),
            'multiline' => self::multilineAddress($address, $zipcode, $city),
            'full'      => self::fullAddress($address, $zipcode, $city),
        ];
    }

    /**
     * Format a short address (address, city).
     *
     * @param  string|null  $address  Street address
     * @param  string|null  $zipcode  Postal code
     * @param  string|null  $city  City name
     */
    public static function shortAddress(?string $address, ?string $zipcode, ?string $city): string
    {
        $parts = array_filter([$address, $city]);

        return implode(', ', $parts);
    }

    /**
     * Format an inline address (address - zipcode - city).
     *
     * @param  string|null  $address  Street address
     * @param  string|null  $zipcode  Postal code
     * @param  string|null  $city  City name
     */
    public static function inlineAddress(?string $address, ?string $zipcode, ?string $city): string
    {
        $parts = array_filter([$address, $zipcode, $city]);

        return implode(' · ', $parts);
    }

    /**
     * Format a multiline address with HTML line breaks.
     *
     * @param  string|null  $address  Street address
     * @param  string|null  $zipcode  Postal code
     * @param  string|null  $city  City name
     */
    public static function multilineAddress(?string $address, ?string $zipcode, ?string $city): string
    {
        $lines = [];

        if (!in_array($address, [null, '', '0'], true)) {
            $lines[] = mb_rtrim($address, ',');
        }

        $cityLine = mb_trim(implode(' ', array_filter([$zipcode, $city])));

        if ($cityLine !== '') {
            $lines[] = $cityLine;
        }

        return implode('<br/>', $lines);
    }

    /**
     * Format a full address (address, zipcode city).
     *
     * @param  string|null  $address  Street address
     * @param  string|null  $zipcode  Postal code
     * @param  string|null  $city  City name
     */
    public static function fullAddress(?string $address, ?string $zipcode, ?string $city): string
    {
        $parts = [];

        if (!in_array($address, [null, '', '0'], true)) {
            $parts[] = $address;
        }

        $location = mb_trim(implode(' ', array_filter([$zipcode, $city])));

        if ($location !== '') {
            $parts[] = $location;
        }

        return implode(', ', $parts);
    }
}
