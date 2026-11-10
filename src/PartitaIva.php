<?php

declare(strict_types=1);

namespace GeneratoreDati\FakerItalia;

/**
 * Partita IVA e codice fiscale numerico delle società (11 cifre).
 *
 * Online tool with the same logic: https://generatore-dati.it/verifica-partita-iva
 */
final class PartitaIva
{
    /** True se la partita IVA è formalmente valida (cifra di controllo e codice ufficio). Accetta il prefisso "IT". */
    public static function isValid(?string $partitaIva): bool
    {
        return self::validate($partitaIva)['status'] === 'valid';
    }

    /**
     * status: empty | format | check | office | valid
     *
     * @return array{status: string, piva: string, expected?: int, office?: string, matricola?: string}
     */
    public static function validate(?string $input): array
    {
        $piva = (string) preg_replace('/^IT/', '', CodiceFiscale::normalize($input));
        if ($piva === '') {
            return ['status' => 'empty', 'piva' => $piva];
        }
        if (! preg_match('/^\d{11}$/', $piva) || $piva === '00000000000') {
            return ['status' => 'format', 'piva' => $piva];
        }
        $expected = self::checkDigit($piva);
        $office = substr($piva, 7, 3);
        if ((int) $piva[10] !== $expected) {
            return ['status' => 'check', 'piva' => $piva, 'expected' => $expected, 'office' => $office];
        }
        if (! self::isValidOfficeCode($office)) {
            return ['status' => 'office', 'piva' => $piva, 'expected' => $expected, 'office' => $office];
        }

        return ['status' => 'valid', 'piva' => $piva, 'expected' => $expected, 'office' => $office, 'matricola' => substr($piva, 0, 7)];
    }

    /** Cifra di controllo (11ª) a partire dalle prime 10 cifre. */
    public static function checkDigit(string $first10): int
    {
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $n = (int) $first10[$i];
            if ($i % 2 === 1) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
        }

        return (10 - $sum % 10) % 10;
    }

    /** Codici ufficio validi: 001–100 (uffici provinciali), 120, 121, 888, 999. */
    public static function isValidOfficeCode(string $office): bool
    {
        $n = (int) $office;

        return ($n >= 1 && $n <= 100) || in_array($n, [120, 121, 888, 999], true);
    }

    /**
     * Componi una partita IVA valida da matricola (7 cifre) e codice ufficio (3 cifre).
     */
    public static function make(string $matricola, string $office): string
    {
        $first10 = str_pad($matricola, 7, '0', STR_PAD_LEFT).str_pad($office, 3, '0', STR_PAD_LEFT);

        return $first10.self::checkDigit($first10);
    }
}
