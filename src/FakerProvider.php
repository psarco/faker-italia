<?php

declare(strict_types=1);

namespace GeneratoreDati\FakerItalia;

use DateTimeImmutable;
use DateTimeInterface;
use Faker\Provider\Base;

/**
 * Faker provider for Italian data that passes real validation.
 *
 * Unlike Faker's built-in it_IT taxId() (random letters and digits), every codice fiscale
 * generated here has a real date, a real place code and the correct check character.
 *
 * Need a quick CSV/JSON/SQL file instead? https://generatore-dati.it/generatore-dati-di-test
 */
class FakerProvider extends Base
{
    /**
     * Codice fiscale valido.
     *
     * @param  string|null  $sex  "M", "F" o null (casuale)
     * @param  DateTimeInterface|string|null  $birthDate  data di nascita (null: tra 18 e 80 anni fa)
     * @param  string|null  $belfiore  codice catastale del luogo di nascita (null: un capoluogo, a volte uno stato estero)
     */
    public function codiceFiscale(
        ?string $sex = null,
        DateTimeInterface|string|null $birthDate = null,
        ?string $belfiore = null,
        ?string $lastName = null,
        ?string $firstName = null,
    ): string {
        $sex = $sex !== null ? strtoupper($sex) : static::randomElement(['M', 'F']);
        $lastName ??= $this->randomLastName();
        $firstName ??= $this->randomFirstName($sex);

        return CodiceFiscale::calculate(
            $lastName,
            $firstName,
            $sex,
            $birthDate ?? $this->randomBirthDate(),
            $belfiore ?? $this->codiceCatastale(),
        );
    }

    /**
     * Persona coerente: nome, cognome, sesso, data e luogo di nascita e codice fiscale corrispondente.
     *
     * @return array{nome: string, cognome: string, sesso: string, data_nascita: string, luogo_nascita: string,
     *               codice_catastale: string, codice_fiscale: string}
     */
    public function personaItaliana(?string $sex = null): array
    {
        $sex = $sex !== null ? strtoupper($sex) : static::randomElement(['M', 'F']);
        $firstName = $this->randomFirstName($sex);
        $lastName = $this->randomLastName();
        $birth = $this->randomBirthDate();
        $code = $this->codiceCatastale();

        return [
            'nome' => $firstName,
            'cognome' => $lastName,
            'sesso' => $sex,
            'data_nascita' => $birth->format('Y-m-d'),
            'luogo_nascita' => (string) Luoghi::name($code),
            'codice_catastale' => $code,
            'codice_fiscale' => CodiceFiscale::calculate($lastName, $firstName, $sex, $birth, $code),
        ];
    }

    /**
     * Partita IVA valida (11 cifre, codice ufficio esistente, cifra di controllo corretta).
     */
    public function partitaIva(bool $withPrefix = false): string
    {
        do {
            $matricola = static::numerify('#######');
        } while ($matricola === '0000000');
        $office = str_pad((string) static::numberBetween(1, 100), 3, '0', STR_PAD_LEFT);

        return ($withPrefix ? 'IT' : '').PartitaIva::make($matricola, $office);
    }

    /**
     * IBAN italiano valido: cifre di controllo e CIN corretti.
     */
    public function ibanItaliano(bool $formatted = false): string
    {
        $iban = Iban::makeItalian(static::numerify('#####'), static::numerify('#####'), static::numerify('############'));

        return $formatted ? Iban::format($iban) : $iban;
    }

    /**
     * Codice catastale (Belfiore) di un capoluogo di provincia o, con probabilità $foreignChance (0–100), di uno stato estero.
     */
    public function codiceCatastale(int $foreignChance = 10): string
    {
        if ($foreignChance > 0 && static::numberBetween(1, 100) <= $foreignChance) {
            return (string) static::randomKey(Luoghi::STATI_ESTERI);
        }

        return (string) static::randomKey(Luoghi::CAPOLUOGHI);
    }

    private function randomBirthDate(): DateTimeImmutable
    {
        $today = new DateTimeImmutable('today');
        $from = $today->modify('-80 years')->getTimestamp();
        $to = $today->modify('-18 years')->getTimestamp();

        return (new DateTimeImmutable)->setTimestamp(static::numberBetween($from, $to))->setTime(0, 0);
    }

    private function randomFirstName(string $sex): string
    {
        $names = $sex === 'F' ? self::FEMALE_NAMES : self::MALE_NAMES;

        return static::randomElement($names);
    }

    private function randomLastName(): string
    {
        return static::randomElement(self::LAST_NAMES);
    }

    private const MALE_NAMES = [
        'Alessandro', 'Andrea', 'Antonio', 'Davide', 'Federico', 'Francesco', 'Gabriele', 'Giovanni', 'Giuseppe', 'Leonardo',
        'Lorenzo', 'Luca', 'Marco', 'Mario', 'Matteo', 'Mattia', 'Paolo', 'Pietro', 'Riccardo', 'Roberto', 'Simone', 'Stefano',
    ];

    private const FEMALE_NAMES = [
        'Alessia', 'Alice', 'Anna', 'Aurora', 'Beatrice', 'Chiara', 'Elena', 'Emma', 'Federica', 'Francesca', 'Giorgia',
        'Giulia', 'Laura', 'Maria', 'Martina', 'Paola', 'Sara', 'Silvia', 'Sofia', 'Valentina', 'Veronica', 'Vittoria',
    ];

    private const LAST_NAMES = [
        'Rossi', 'Russo', 'Ferrari', 'Esposito', 'Bianchi', 'Romano', 'Colombo', 'Ricci', 'Marino', 'Greco', 'Bruno',
        'Gallo', 'Conti', 'De Luca', 'Mancini', 'Costa', 'Giordano', 'Rizzo', 'Lombardi', 'Moretti', 'Barbieri', 'Fontana',
        'Santoro', 'Mariani', 'Rinaldi', 'Caruso', 'Ferrara', 'Galli', 'Martini', 'Leone', 'Longo', 'Gentile', 'D\'Angelo',
    ];
}
