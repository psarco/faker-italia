# Faker Italia

[![Tests](https://github.com/generatore-dati/faker-italia/actions/workflows/tests.yml/badge.svg)](https://github.com/generatore-dati/faker-italia/actions/workflows/tests.yml)
[![Latest version](https://img.shields.io/packagist/v/generatore-dati/faker-italia.svg)](https://packagist.org/packages/generatore-dati/faker-italia)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

Dati di test italiani **che superano davvero la validazione**: codice fiscale, partita IVA e IBAN per [Faker](https://fakerphp.org), più i validatori e le regole Laravel per controllarli.

> 🇬🇧 [English below](#english)

## Perché

Il provider `it_IT` di Faker genera codici fiscali con lettere e cifre casuali e IBAN con un CIN casuale. Su 10.000 valori generati:

| | Faker `it_IT` | Faker Italia |
|---|---|---|
| Codice fiscale valido | 1,1% (`taxId()`) | **100%** |
| IBAN italiano valido (CIN incluso) | 3,6% (`iban('IT')`) | **100%** |
| Partita IVA valida (codice ufficio incluso) | 84,3% (`vat()`) | **100%** |

Se la tua applicazione valida questi campi (e dovrebbe), le factory e i seeder con Faker standard falliscono a caso.

## Installazione

```bash
composer require --dev generatore-dati/faker-italia
```

Requisiti: PHP 8.1+, `fakerphp/faker` 1.20+.

## Uso con Faker

```php
use GeneratoreDati\FakerItalia\FakerProvider;

$faker = Faker\Factory::create('it_IT');
$faker->addProvider(new FakerProvider($faker));

$faker->codiceFiscale();                         // "GRCCHR58E68L219K"
$faker->codiceFiscale('F', '1990-05-12', 'F205'); // donna, nata il 12/05/1990 a Milano
$faker->partitaIva();                            // "10446710534"
$faker->partitaIva(withPrefix: true);            // "IT10446710534"
$faker->ibanItaliano();                          // "IT31E5666885391743795602475"
$faker->ibanItaliano(formatted: true);           // "IT31 E566 6885 3917 4379 5602 475"
$faker->codiceCatastale();                       // "H501" (un capoluogo, a volte uno stato estero)

$faker->personaItaliana();
// [
//     'nome' => 'Chiara', 'cognome' => 'Greco', 'sesso' => 'F',
//     'data_nascita' => '1958-05-28', 'luogo_nascita' => 'Torino',
//     'codice_catastale' => 'L219', 'codice_fiscale' => 'GRCCHR58E68L219K',
// ]
```

Con `$faker->seed(123)` i valori sono sempre gli stessi, come per il resto di Faker.

## Uso con Laravel

Il provider si registra da solo (package discovery): funziona subito in factory, seeder e test.

```php
// database/factories/CustomerFactory.php
public function definition(): array
{
    $persona = fake()->personaItaliana();

    return [
        'first_name' => $persona['nome'],
        'last_name' => $persona['cognome'],
        'tax_code' => $persona['codice_fiscale'],
        'vat_number' => fake()->partitaIva(),
        'iban' => fake()->ibanItaliano(),
    ];
}
```

### Regole di validazione

```php
use GeneratoreDati\FakerItalia\Laravel\Rules\CodiceFiscale;
use GeneratoreDati\FakerItalia\Laravel\Rules\Iban;
use GeneratoreDati\FakerItalia\Laravel\Rules\PartitaIva;

$request->validate([
    'codice_fiscale' => ['required', new CodiceFiscale],
    'partita_iva' => ['nullable', new PartitaIva],
    'iban' => ['required', new Iban(onlyItalian: true)],
]);
```

## Validatori (anche senza Faker né Laravel)

```php
use GeneratoreDati\FakerItalia\CodiceFiscale;
use GeneratoreDati\FakerItalia\Iban;
use GeneratoreDati\FakerItalia\PartitaIva;

CodiceFiscale::isValid('RSSMRA80A01H501U');      // true
CodiceFiscale::validate('RSSMRA80A01H501U');     // sesso, data di nascita, codice catastale, omocodia
CodiceFiscale::calculate('Rossi', 'Mario', 'M', '1980-01-01', 'H501'); // "RSSMRA80A01H501U"

PartitaIva::isValid('IT10446710534');            // true
Iban::isValid('IT60 X054 2811 1010 0000 0123 456'); // true, CIN incluso
Iban::makeItalian('05428', '11101', '000000123456'); // "IT60X0542811101000000123456"
```

`validate()` restituisce anche il motivo dell'errore (`format`, `check`, `date`, `office`, `length`, `cin`…), utile per mostrare messaggi chiari.

## Strumenti online

Ti serve un file al volo, senza scrivere codice? Su **[generatore-dati.it](https://generatore-dati.it)** trovi gli stessi algoritmi nel browser, gratis e senza registrazione:

- [Generatore di dati di test](https://generatore-dati.it/generatore-dati-di-test): fino a 1.000 righe in CSV, JSON o SQL
- [Verifica codice fiscale](https://generatore-dati.it/verifica-codice-fiscale) e [calcolo del codice fiscale](https://generatore-dati.it/calcolo-codice-fiscale)
- [Generatore di partite IVA](https://generatore-dati.it/generatore-partita-iva) e [verifica partita IVA](https://generatore-dati.it/verifica-partita-iva)
- [Generatore di IBAN](https://generatore-dati.it/generatore-iban) e [verifica IBAN](https://generatore-dati.it/verifica-iban)
- [Codici catastali](https://generatore-dati.it/codici-catastali) di tutti i comuni e gli stati esteri

## Nota

I valori generati sono **formalmente validi ma inventati** e possono coincidere per caso con quelli di persone o aziende reali. Usali solo per test e sviluppo, mai per identificarti o per documenti reali.

---

## English

Italian test data for [Faker](https://fakerphp.org) that **passes real validation**: codice fiscale (tax code), partita IVA (VAT number) and IBAN, plus validators and Laravel validation rules.

Faker's built-in `it_IT` provider returns random tax codes (about 1% are valid) and Italian IBANs with a random CIN (about 4% valid). If your app validates these fields, factories and seeders fail randomly. This package fixes that.

```bash
composer require --dev generatore-dati/faker-italia
```

```php
$faker->addProvider(new GeneratoreDati\FakerItalia\FakerProvider($faker));

$faker->codiceFiscale();      // valid tax code: real date, real place code, correct check char
$faker->partitaIva();         // valid VAT number
$faker->ibanItaliano();       // valid Italian IBAN, CIN included
$faker->personaItaliana();    // consistent person: name, birth date, birthplace and matching tax code
```

In Laravel the provider is registered automatically, and `CodiceFiscale`, `PartitaIva` and `Iban` validation rules are included. The same algorithms are available as free online tools at [generatore-dati.it](https://generatore-dati.it).

## License

MIT. See [LICENSE](LICENSE).
