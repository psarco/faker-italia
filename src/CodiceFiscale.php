<?php

declare(strict_types=1);

namespace GeneratoreDati\FakerItalia;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * Codice fiscale delle persone fisiche: verifica, decodifica e calcolo.
 *
 * Online tool with the same logic: https://generatore-dati.it/verifica-codice-fiscale
 */
final class CodiceFiscale
{
    public const MONTH_LETTERS = 'ABCDEHLMPRST';

    /** Lettere che sostituiscono le cifre 0–9 nei codici "omocodici". */
    public const OMOCODIA = 'LMNPQRSTUV';

    private const OMO_POSITIONS = [6, 7, 9, 10, 12, 13, 14];

    private const PATTERN = '/^[A-Z]{6}[0-9LMNPQRSTUV]{2}[ABCDEHLMPRST][0-9LMNPQRSTUV]{2}[A-Z][0-9LMNPQRSTUV]{3}[A-Z]$/';

    private const ODD = [
        '0' => 1, '1' => 0, '2' => 5, '3' => 7, '4' => 9, '5' => 13, '6' => 15, '7' => 17, '8' => 19, '9' => 21,
        'A' => 1, 'B' => 0, 'C' => 5, 'D' => 7, 'E' => 9, 'F' => 13, 'G' => 15, 'H' => 17, 'I' => 19, 'J' => 21,
        'K' => 2, 'L' => 4, 'M' => 18, 'N' => 20, 'O' => 11, 'P' => 3, 'Q' => 6, 'R' => 8, 'S' => 12, 'T' => 14,
        'U' => 16, 'V' => 10, 'W' => 22, 'X' => 25, 'Y' => 24, 'Z' => 23,
    ];

    /** True se il codice fiscale è formalmente valido (formato, carattere di controllo e data). */
    public static function isValid(?string $codiceFiscale): bool
    {
        return self::validate($codiceFiscale)['status'] === 'valid';
    }

    /**
     * Verifica e decodifica un codice fiscale.
     *
     * status: empty | format | check | date | valid
     *
     * @return array{status: string, cf: string, expected?: string, omocode?: bool, sex?: string,
     *               birth?: array{year: int, month: int, day: int}, belfiore?: string, foreign?: bool}
     */
    public static function validate(?string $input, ?DateTimeInterface $today = null): array
    {
        $cf = self::normalize($input);
        if ($cf === '') {
            return ['status' => 'empty', 'cf' => $cf];
        }
        if (! preg_match(self::PATTERN, $cf)) {
            return ['status' => 'format', 'cf' => $cf];
        }

        $expected = self::checkChar($cf);
        if ($cf[15] !== $expected) {
            return ['status' => 'check', 'cf' => $cf, 'expected' => $expected];
        }

        $plain = self::deOmocode($cf);
        $month = strpos(self::MONTH_LETTERS, $plain[8]) + 1;
        $day = (int) substr($plain, 9, 2);
        $sex = $day > 40 ? 'F' : 'M';
        if ($day > 40) {
            $day -= 40;
        }
        $year = self::fullYear((int) substr($plain, 6, 2), $today);
        if ($day < 1 || ! checkdate($month, $day, $year)) {
            return ['status' => 'date', 'cf' => $cf, 'expected' => $expected];
        }

        return [
            'status' => 'valid',
            'cf' => $cf,
            'expected' => $expected,
            'omocode' => $plain !== $cf,
            'sex' => $sex,
            'birth' => ['year' => $year, 'month' => $month, 'day' => $day],
            'belfiore' => substr($plain, 11, 4),
            'foreign' => $plain[11] === 'Z',
        ];
    }

    /**
     * Calcola il codice fiscale (senza omocodia).
     *
     * @param  string  $sex  "M" o "F"
     * @param  DateTimeInterface|string  $birth  data di nascita (oggetto o stringa YYYY-MM-DD)
     * @param  string  $belfiore  codice catastale del comune o dello stato estero (es. H501 Roma, Z404 USA)
     */
    public static function calculate(string $surname, string $name, string $sex, DateTimeInterface|string $birth, string $belfiore): string
    {
        if ($birth instanceof DateTimeInterface) {
            $birth = $birth->format('Y-m-d');
        }
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $birth, $m) || ! checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            throw new InvalidArgumentException('Invalid birth date, expected YYYY-MM-DD');
        }
        $belfiore = strtoupper($belfiore);
        if (! preg_match('/^[A-Z]\d{3}$/', $belfiore)) {
            throw new InvalidArgumentException('Invalid codice catastale (Belfiore), expected e.g. H501');
        }
        $sex = strtoupper($sex);
        if ($sex !== 'M' && $sex !== 'F') {
            throw new InvalidArgumentException('Invalid sex, expected "M" or "F"');
        }
        $day = (int) $m[3] + ($sex === 'F' ? 40 : 0);

        $first15 = self::surnameCode($surname).self::nameCode($name)
            .substr($m[1], 2).self::MONTH_LETTERS[(int) $m[2] - 1]
            .str_pad((string) $day, 2, '0', STR_PAD_LEFT).$belfiore;

        return $first15.self::checkChar($first15);
    }

    /** Carattere di controllo (16°) a partire dai primi 15 caratteri. */
    public static function checkChar(string $first15): string
    {
        return self::controlLetter(strtoupper(substr($first15, 0, 15)));
    }

    /**
     * Lettera di controllo "all'italiana": usata dal codice fiscale e dal CIN dell'IBAN.
     *
     * @internal
     */
    public static function controlLetter(string $s): string
    {
        $sum = 0;
        $len = strlen($s);
        for ($i = 0; $i < $len; $i++) {
            $c = $s[$i];
            if ($i % 2 === 0) {
                $sum += self::ODD[$c];
            } else {
                $sum += ctype_digit($c) ? (int) $c : ord($c) - 65;
            }
        }

        return chr(65 + $sum % 26);
    }

    public static function normalize(?string $input): string
    {
        return (string) preg_replace('/[\s.\-]/', '', strtoupper((string) $input));
    }

    /** Sostituisce le lettere dell'omocodia con le cifre originali. */
    public static function deOmocode(string $cf): string
    {
        foreach (self::OMO_POSITIONS as $p) {
            $i = strpos(self::OMOCODIA, $cf[$p]);
            if ($i !== false) {
                $cf[$p] = (string) $i;
            }
        }

        return $cf;
    }

    public static function surnameCode(string $surname): string
    {
        [$cons, $vows] = self::split(self::cleanName($surname));

        return substr($cons.$vows.'XXX', 0, 3);
    }

    public static function nameCode(string $name): string
    {
        [$cons, $vows] = self::split(self::cleanName($name));
        if (strlen($cons) >= 4) {
            return $cons[0].$cons[2].$cons[3];
        }

        return substr($cons.$vows.'XXX', 0, 3);
    }

    private static function fullYear(int $yy, ?DateTimeInterface $today = null): int
    {
        $current = (int) ($today ?? new DateTimeImmutable)->format('Y');
        $century = intdiv($current, 100) * 100;

        return $century + $yy <= $current ? $century + $yy : $century - 100 + $yy;
    }

    private static function cleanName(string $s): string
    {
        if (class_exists(\Normalizer::class)) {
            $s = \Normalizer::normalize($s, \Normalizer::FORM_D) ?: $s;
            $s = (string) preg_replace('/\p{Mn}/u', '', $s);
        } else {
            $s = strtr($s, [
                'à' => 'a', 'á' => 'a', 'è' => 'e', 'é' => 'e', 'ì' => 'i', 'í' => 'i', 'ò' => 'o', 'ó' => 'o', 'ù' => 'u', 'ú' => 'u',
                'À' => 'A', 'Á' => 'A', 'È' => 'E', 'É' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Ò' => 'O', 'Ó' => 'O', 'Ù' => 'U', 'Ú' => 'U',
            ]);
        }

        return (string) preg_replace('/[^A-Z]/', '', strtoupper($s));
    }

    /** @return array{0: string, 1: string} consonanti, vocali */
    private static function split(string $s): array
    {
        $cons = $vows = '';
        foreach (str_split($s) as $c) {
            if (str_contains('AEIOU', $c)) {
                $vows .= $c;
            } else {
                $cons .= $c;
            }
        }

        return [$cons, $vows];
    }
}
