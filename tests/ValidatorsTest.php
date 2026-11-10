<?php

declare(strict_types=1);

namespace GeneratoreDati\FakerItalia\Tests;

use GeneratoreDati\FakerItalia\CodiceFiscale;
use GeneratoreDati\FakerItalia\Iban;
use GeneratoreDati\FakerItalia\PartitaIva;
use PHPUnit\Framework\TestCase;

final class ValidatorsTest extends TestCase
{
    public function test_codice_fiscale_calculate(): void
    {
        $this->assertSame('RSSMRA80A01H501U', CodiceFiscale::calculate('Rossi', 'Mario', 'M', '1980-01-01', 'H501'));
        // Donna: giorno + 40; nome con 4+ consonanti; cognome corto
        $this->assertSame('LOXGLI90E52F205', substr(CodiceFiscale::calculate('Lo', 'Giulia', 'F', '1990-05-12', 'F205'), 0, 15));
        // Accenti e apostrofi vengono ignorati
        $this->assertSame(
            CodiceFiscale::calculate('DAngelo', 'Niccolo', 'M', '1975-12-31', 'Z404'),
            CodiceFiscale::calculate('D\'Angelo', 'Niccolò', 'M', new \DateTimeImmutable('1975-12-31'), 'z404'),
        );
    }

    public function test_codice_fiscale_validate(): void
    {
        $this->assertTrue(CodiceFiscale::isValid('RSSMRA80A01H501U'));
        $this->assertTrue(CodiceFiscale::isValid('rss mra 80a01 h501u'));
        $this->assertSame('check', CodiceFiscale::validate('RSSMRA80A01H501A')['status']);
        $this->assertSame('format', CodiceFiscale::validate('RSSMRA80A01H501')['status']);
        $this->assertSame('empty', CodiceFiscale::validate('')['status']);

        $r = CodiceFiscale::validate('RSSMRA80A01H501U');
        $this->assertSame('M', $r['sex']);
        $this->assertSame(['year' => 1980, 'month' => 1, 'day' => 1], $r['birth']);
        $this->assertSame('H501', $r['belfiore']);
        $this->assertFalse($r['omocode']);

        // Omocodia: l'ultima cifra del codice catastale (1 -> M) con il nuovo carattere di controllo
        $omo = 'RSSMRA80A01H50M';
        $omo .= CodiceFiscale::checkChar($omo);
        $r = CodiceFiscale::validate($omo);
        $this->assertSame('valid', $r['status']);
        $this->assertTrue($r['omocode']);
        $this->assertSame('H501', $r['belfiore']);
    }

    public function test_partita_iva(): void
    {
        $this->assertSame(3, PartitaIva::checkDigit('1234567890'));
        $this->assertSame('office', PartitaIva::validate('12345678903')['status']);
        $this->assertSame('check', PartitaIva::validate('12345678904')['status']);
        $this->assertSame('format', PartitaIva::validate('00000000000')['status']);

        $valid = PartitaIva::make('1234567', '001');
        $this->assertTrue(PartitaIva::isValid($valid));
        $this->assertTrue(PartitaIva::isValid('IT'.$valid));
    }

    public function test_iban(): void
    {
        $this->assertTrue(Iban::isValid('IT60X0542811101000000123456'));
        $this->assertTrue(Iban::isValid('IT60 X054 2811 1010 0000 0123 456'));
        $this->assertTrue(Iban::isValid('DE89370400440532013000'));
        $this->assertSame('check', Iban::validate('IT61X0542811101000000123456')['status']);
        $this->assertSame('length', Iban::validate('IT60X054281110100000012345')['status']);
        // Cifre di controllo corrette ma CIN sbagliato: lo scoprono solo i validatori italiani
        $bban = 'A0542811101000000123456';
        $this->assertSame('cin', Iban::validate('IT'.Iban::checkDigits('IT', $bban).$bban)['status']);

        $this->assertSame('IT60X0542811101000000123456', Iban::makeItalian('05428', '11101', '000000123456'));
        $this->assertSame('IT60 X054 2811 1010 0000 0123 456', Iban::format('IT60X0542811101000000123456'));
    }
}
