<?php

declare(strict_types=1);

namespace GeneratoreDati\FakerItalia;

/**
 * IBAN: verifica per tutti i paesi del registro SWIFT, con controllo del CIN per Italia e San Marino.
 *
 * Online tool with the same logic: https://generatore-dati.it/verifica-iban
 */
final class Iban
{
    public const LENGTHS = [
        'AD' => 24, 'AE' => 23, 'AL' => 28, 'AT' => 20, 'AZ' => 28, 'BA' => 20, 'BE' => 16, 'BG' => 22, 'BH' => 22,
        'BI' => 27, 'BR' => 29, 'BY' => 28, 'CH' => 21, 'CR' => 22, 'CY' => 28, 'CZ' => 24, 'DE' => 22, 'DJ' => 27,
        'DK' => 18, 'DO' => 28, 'EE' => 20, 'EG' => 29, 'ES' => 24, 'FI' => 18, 'FK' => 18, 'FO' => 18, 'FR' => 27,
        'GB' => 22, 'GE' => 22, 'GI' => 23, 'GL' => 18, 'GR' => 27, 'GT' => 28, 'HN' => 28, 'HR' => 21, 'HU' => 28,
        'IE' => 22, 'IL' => 23, 'IQ' => 23, 'IS' => 26, 'IT' => 27, 'JO' => 30, 'KW' => 30, 'KZ' => 20, 'LB' => 28,
        'LC' => 32, 'LI' => 21, 'LT' => 20, 'LU' => 20, 'LV' => 21, 'LY' => 25, 'MC' => 27, 'MD' => 24, 'ME' => 22,
        'MK' => 19, 'MN' => 20, 'MR' => 27, 'MT' => 31, 'MU' => 30, 'NI' => 28, 'NL' => 18, 'NO' => 15, 'OM' => 23,
        'PK' => 24, 'PL' => 28, 'PS' => 29, 'PT' => 25, 'QA' => 29, 'RO' => 24, 'RS' => 22, 'RU' => 33, 'SA' => 24,
        'SC' => 31, 'SD' => 18, 'SE' => 24, 'SI' => 19, 'SK' => 24, 'SM' => 27, 'SO' => 23, 'ST' => 25, 'SV' => 28,
        'TL' => 23, 'TN' => 24, 'TR' => 26, 'UA' => 29, 'VA' => 22, 'VG' => 24, 'XK' => 20, 'YE' => 30,
    ];

    /** True se l'IBAN è valido (lunghezza, cifre di controllo e, per IT/SM, CIN). */
    public static function isValid(?string $iban): bool
    {
        return self::validate($iban)['status'] === 'valid';
    }

    /**
     * status: empty | format | length | check | cin | valid
     *
     * @return array<string, mixed>
     */
    public static function validate(?string $input): array
    {
        $iban = CodiceFiscale::normalize($input);
        if ($iban === '') {
            return ['status' => 'empty', 'iban' => $iban];
        }
        if (! preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{8,30}$/', $iban)) {
            return ['status' => 'format', 'iban' => $iban];
        }

        $country = substr($iban, 0, 2);
        $expectedLength = self::LENGTHS[$country] ?? null;
        $base = ['iban' => $iban, 'country' => $country, 'formatted' => self::format($iban), 'knownCountry' => $expectedLength !== null];
        if ($expectedLength !== null && strlen($iban) !== $expectedLength) {
            return $base + ['status' => 'length', 'expectedLength' => $expectedLength];
        }
        $bban = substr($iban, 4);
        if (self::mod97($bban.substr($iban, 0, 4)) !== 1) {
            return $base + ['status' => 'check', 'expectedCheck' => self::checkDigits($country, $bban)];
        }

        if ($country === 'IT' || $country === 'SM') {
            $it = ['cin' => $bban[0], 'abi' => substr($bban, 1, 5), 'cab' => substr($bban, 6, 5), 'conto' => substr($bban, 11)];
            $it['expectedCin'] = ctype_upper($it['cin']) && ctype_digit($it['abi'].$it['cab'])
                ? self::italianCin($it['abi'], $it['cab'], $it['conto'])
                : null;

            return $base + ['status' => $it['expectedCin'] === $it['cin'] ? 'valid' : 'cin', 'it' => $it];
        }

        return $base + ['status' => 'valid'];
    }

    /**
     * Componi un IBAN italiano valido (CIN e cifre di controllo calcolati) da ABI, CAB e numero di conto.
     */
    public static function makeItalian(string $abi, string $cab, string $conto, string $country = 'IT'): string
    {
        $conto = strtoupper(str_pad($conto, 12, '0', STR_PAD_LEFT));
        $bban = self::italianCin($abi, $cab, $conto).$abi.$cab.$conto;

        return $country.self::checkDigits($country, $bban).$bban;
    }

    public static function italianCin(string $abi, string $cab, string $conto): string
    {
        return CodiceFiscale::controlLetter($abi.$cab.strtoupper($conto));
    }

    public static function checkDigits(string $country, string $bban): string
    {
        return str_pad((string) (98 - self::mod97($bban.$country.'00')), 2, '0', STR_PAD_LEFT);
    }

    /** Formato leggibile a gruppi di 4: "IT60 X054 2811 1010 0000 0123 456". */
    public static function format(string $iban): string
    {
        return trim(chunk_split(CodiceFiscale::normalize($iban), 4, ' '));
    }

    private static function mod97(string $alnum): int
    {
        $rem = 0;
        foreach (str_split($alnum) as $c) {
            $v = ctype_alpha($c) ? (string) (ord($c) - 55) : $c;
            foreach (str_split($v) as $d) {
                $rem = ($rem * 10 + (int) $d) % 97;
            }
        }

        return $rem;
    }
}
