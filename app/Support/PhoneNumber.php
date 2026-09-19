<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * @return array<string, string>
     */
    public static function countryCodes(): array
    {
        return [
            '92' => '+92 Pakistan',
            '93' => '+93 Afghanistan',
            '61' => '+61 Australia',
            '973' => '+973 Bahrain',
            '880' => '+880 Bangladesh',
            '55' => '+55 Brazil',
            '1' => '+1 Canada / USA',
            '86' => '+86 China',
            '20' => '+20 Egypt',
            '33' => '+33 France',
            '49' => '+49 Germany',
            '852' => '+852 Hong Kong',
            '91' => '+91 India',
            '62' => '+62 Indonesia',
            '98' => '+98 Iran',
            '964' => '+964 Iraq',
            '353' => '+353 Ireland',
            '39' => '+39 Italy',
            '81' => '+81 Japan',
            '962' => '+962 Jordan',
            '254' => '+254 Kenya',
            '965' => '+965 Kuwait',
            '961' => '+961 Lebanon',
            '60' => '+60 Malaysia',
            '52' => '+52 Mexico',
            '212' => '+212 Morocco',
            '31' => '+31 Netherlands',
            '64' => '+64 New Zealand',
            '234' => '+234 Nigeria',
            '968' => '+968 Oman',
            '63' => '+63 Philippines',
            '974' => '+974 Qatar',
            '7' => '+7 Russia',
            '966' => '+966 Saudi Arabia',
            '65' => '+65 Singapore',
            '27' => '+27 South Africa',
            '82' => '+82 South Korea',
            '94' => '+94 Sri Lanka',
            '46' => '+46 Sweden',
            '41' => '+41 Switzerland',
            '66' => '+66 Thailand',
            '90' => '+90 Turkey',
            '256' => '+256 Uganda',
            '971' => '+971 United Arab Emirates',
            '44' => '+44 United Kingdom',
            '84' => '+84 Vietnam',
            '967' => '+967 Yemen',
        ];
    }

    public static function defaultCountryCode(): string
    {
        return '92';
    }

    public static function normalize(?string $countryCode, ?string $number): string
    {
        $digits = preg_replace('/\D+/', '', ($countryCode ?? '').($number ?? '')) ?? '';

        return ltrim($digits, '0') === '' ? $digits : ltrim($digits, '');
    }

    public static function digits(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone) ?? '';
    }

    public static function matches(?string $submitted, ?string $stored): bool
    {
        $left = self::digits($submitted);
        $right = self::digits($stored);

        if ($left === '' || $right === '') {
            return false;
        }

        return $left === $right
            || str_ends_with($left, $right)
            || str_ends_with($right, $left);
    }

    public static function mask(?string $phone): string
    {
        $digits = self::digits($phone);

        if (strlen($digits) < 3) {
            return '***';
        }

        return '***'.substr($digits, -3);
    }

    public static function display(?string $phone): string
    {
        $digits = self::digits($phone);

        if ($digits === '') {
            return '—';
        }

        if (str_starts_with($digits, '92') && strlen($digits) >= 12) {
            return '+92 '.substr($digits, 2, 3).' '.substr($digits, 5);
        }

        return '+'.$digits;
    }

    public static function localPart(?string $phone): string
    {
        $digits = self::digits($phone);

        if (str_starts_with($digits, '92')) {
            return substr($digits, 2);
        }

        return $digits;
    }
}
