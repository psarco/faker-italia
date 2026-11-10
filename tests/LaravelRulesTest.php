<?php

declare(strict_types=1);

namespace GeneratoreDati\FakerItalia\Tests;

use GeneratoreDati\FakerItalia\Laravel\Rules\CodiceFiscale;
use GeneratoreDati\FakerItalia\Laravel\Rules\Iban;
use GeneratoreDati\FakerItalia\Laravel\Rules\PartitaIva;
use Illuminate\Contracts\Validation\ValidationRule;
use PHPUnit\Framework\TestCase;

final class LaravelRulesTest extends TestCase
{
    private function fails(ValidationRule $rule, mixed $value): ?string
    {
        $message = null;
        $rule->validate('campo', $value, function (string $m) use (&$message): void {
            $message = $m;
        });

        return $message;
    }

    public function test_rules(): void
    {
        $this->assertSame(null, $this->fails(new CodiceFiscale, 'RSSMRA80A01H501U'));
        $this->assertSame('Il campo :attribute non è un codice fiscale valido.', $this->fails(new CodiceFiscale, 'RSSMRA80A01H501A'));
        $this->assertSame('Il campo :attribute non è un codice fiscale valido.', $this->fails(new CodiceFiscale, ['x']));

        $this->assertSame(null, $this->fails(new PartitaIva, 'IT'.\GeneratoreDati\FakerItalia\PartitaIva::make('1234567', '001')));
        $this->assertSame('Il campo :attribute non è una partita IVA valida.', $this->fails(new PartitaIva, '12345678903'));

        $this->assertSame(null, $this->fails(new Iban, 'DE89370400440532013000'));
        $this->assertSame(null, $this->fails(new Iban(onlyItalian: true), 'IT60X0542811101000000123456'));
        $this->assertSame('Il campo :attribute deve essere un IBAN italiano.', $this->fails(new Iban(onlyItalian: true), 'DE89370400440532013000'));
        $this->assertSame('Il campo :attribute non è un IBAN valido.', $this->fails(new Iban, 'IT00X0542811101000000123456'));
    }
}
