<?php

declare(strict_types=1);

namespace GeneratoreDati\FakerItalia\Tests;

use Faker\Factory;
use Faker\Generator;
use GeneratoreDati\FakerItalia\CodiceFiscale;
use GeneratoreDati\FakerItalia\FakerProvider;
use GeneratoreDati\FakerItalia\Iban;
use GeneratoreDati\FakerItalia\Luoghi;
use GeneratoreDati\FakerItalia\PartitaIva;
use PHPUnit\Framework\TestCase;

final class FakerProviderTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create('it_IT');
        $this->faker->addProvider(new FakerProvider($this->faker));
    }

    public function test_generated_values_are_valid(): void
    {
        for ($i = 0; $i < 500; $i++) {
            $this->assertTrue(CodiceFiscale::isValid($this->faker->codiceFiscale()));
            $this->assertTrue(PartitaIva::isValid($this->faker->partitaIva()));
            $this->assertTrue(Iban::isValid($this->faker->ibanItaliano()));
        }
        $this->assertSame('IT', substr($this->faker->partitaIva(withPrefix: true), 0, 2));
        $this->assertSame(33, strlen($this->faker->ibanItaliano(formatted: true)));
    }

    public function test_codice_fiscale_options(): void
    {
        $cf = $this->faker->codiceFiscale('F', '1985-07-14', 'F205');
        $r = CodiceFiscale::validate($cf);
        $this->assertSame('F', $r['sex']);
        $this->assertSame(['year' => 1985, 'month' => 7, 'day' => 14], $r['birth']);
        $this->assertSame('F205', $r['belfiore']);

        $this->assertSame('RSSMRA80A01H501U', $this->faker->codiceFiscale('M', '1980-01-01', 'H501', 'Rossi', 'Mario'));
    }

    public function test_persona_is_consistent(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $p = $this->faker->personaItaliana();
            $this->assertSame(
                CodiceFiscale::calculate($p['cognome'], $p['nome'], $p['sesso'], $p['data_nascita'], $p['codice_catastale']),
                $p['codice_fiscale'],
            );
            $this->assertTrue($p['luogo_nascita'] !== '');
            $age = (new \DateTimeImmutable($p['data_nascita']))->diff(new \DateTimeImmutable('today'))->y;
            $this->assertTrue($age >= 18 && $age <= 80);
        }
        $this->assertSame('F', $this->faker->personaItaliana('f')['sesso']);
    }

    public function test_seed_makes_output_repeatable(): void
    {
        $this->faker->seed(42);
        $a = [$this->faker->codiceFiscale(), $this->faker->partitaIva(), $this->faker->ibanItaliano()];
        $this->faker->seed(42);
        $b = [$this->faker->codiceFiscale(), $this->faker->partitaIva(), $this->faker->ibanItaliano()];
        $this->assertSame($a, $b);
    }

    public function test_place_codes(): void
    {
        $this->assertSame('Roma', Luoghi::name('H501'));
        $this->assertSame('Perù', Luoghi::name('z611'));
        $this->assertSame(null, Luoghi::name('X999'));
        foreach (array_keys(Luoghi::CAPOLUOGHI + Luoghi::STATI_ESTERI) as $code) {
            $this->assertSame(1, preg_match('/^[A-Z]\d{3}$/', $code));
        }
        $this->assertSame('Z', $this->faker->codiceCatastale(100)[0]);
        $this->assertTrue(isset(Luoghi::CAPOLUOGHI[$this->faker->codiceCatastale(0)]));
    }
}
