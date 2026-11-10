<?php

declare(strict_types=1);

namespace GeneratoreDati\FakerItalia;

/**
 * Codici catastali (codici Belfiore) usati per generare luoghi di nascita realistici:
 * i capoluoghi di provincia e gli stati esteri più frequenti.
 *
 * Every code, for all 7,900+ Italian municipalities and foreign states:
 * https://generatore-dati.it/codici-catastali
 */
final class Luoghi
{
    /** @var array<string, array{0: string, 1: string}> codice => [comune, sigla provincia] */
    public const CAPOLUOGHI = [
        'A089' => ['Agrigento', 'AG'],
        'A182' => ['Alessandria', 'AL'],
        'A271' => ['Ancona', 'AN'],
        'A326' => ['Aosta', 'AO'],
        'A462' => ['Ascoli Piceno', 'AP'],
        'A345' => ['L\'Aquila', 'AQ'],
        'A390' => ['Arezzo', 'AR'],
        'A479' => ['Asti', 'AT'],
        'A509' => ['Avellino', 'AV'],
        'A662' => ['Bari', 'BA'],
        'A794' => ['Bergamo', 'BG'],
        'A859' => ['Biella', 'BI'],
        'A757' => ['Belluno', 'BL'],
        'A783' => ['Benevento', 'BN'],
        'A944' => ['Bologna', 'BO'],
        'B180' => ['Brindisi', 'BR'],
        'B157' => ['Brescia', 'BS'],
        'A669' => ['Barletta', 'BT'],
        'A952' => ['Bolzano', 'BZ'],
        'B354' => ['Cagliari', 'CA'],
        'B519' => ['Campobasso', 'CB'],
        'B963' => ['Caserta', 'CE'],
        'C632' => ['Chieti', 'CH'],
        'B429' => ['Caltanissetta', 'CL'],
        'D205' => ['Cuneo', 'CN'],
        'C933' => ['Como', 'CO'],
        'D150' => ['Cremona', 'CR'],
        'D086' => ['Cosenza', 'CS'],
        'C351' => ['Catania', 'CT'],
        'C352' => ['Catanzaro', 'CZ'],
        'C342' => ['Enna', 'EN'],
        'D704' => ['Forlì', 'FC'],
        'D548' => ['Ferrara', 'FE'],
        'D643' => ['Foggia', 'FG'],
        'D612' => ['Firenze', 'FI'],
        'D542' => ['Fermo', 'FM'],
        'D810' => ['Frosinone', 'FR'],
        'D969' => ['Genova', 'GE'],
        'E098' => ['Gorizia', 'GO'],
        'E202' => ['Grosseto', 'GR'],
        'E290' => ['Imperia', 'IM'],
        'E335' => ['Isernia', 'IS'],
        'D122' => ['Crotone', 'KR'],
        'E507' => ['Lecco', 'LC'],
        'E506' => ['Lecce', 'LE'],
        'E625' => ['Livorno', 'LI'],
        'E648' => ['Lodi', 'LO'],
        'E472' => ['Latina', 'LT'],
        'E715' => ['Lucca', 'LU'],
        'F704' => ['Monza', 'MB'],
        'E783' => ['Macerata', 'MC'],
        'F158' => ['Messina', 'ME'],
        'F205' => ['Milano', 'MI'],
        'E897' => ['Mantova', 'MN'],
        'F257' => ['Modena', 'MO'],
        'F023' => ['Massa', 'MS'],
        'F052' => ['Matera', 'MT'],
        'F839' => ['Napoli', 'NA'],
        'F952' => ['Novara', 'NO'],
        'F979' => ['Nuoro', 'NU'],
        'G113' => ['Oristano', 'OR'],
        'G273' => ['Palermo', 'PA'],
        'G535' => ['Piacenza', 'PC'],
        'G224' => ['Padova', 'PD'],
        'G482' => ['Pescara', 'PE'],
        'G478' => ['Perugia', 'PG'],
        'G702' => ['Pisa', 'PI'],
        'G888' => ['Pordenone', 'PN'],
        'G999' => ['Prato', 'PO'],
        'G337' => ['Parma', 'PR'],
        'G713' => ['Pistoia', 'PT'],
        'G479' => ['Pesaro', 'PU'],
        'G388' => ['Pavia', 'PV'],
        'G942' => ['Potenza', 'PZ'],
        'H199' => ['Ravenna', 'RA'],
        'H224' => ['Reggio di Calabria', 'RC'],
        'H223' => ['Reggio nell\'Emilia', 'RE'],
        'H163' => ['Ragusa', 'RG'],
        'H282' => ['Rieti', 'RI'],
        'H501' => ['Roma', 'RM'],
        'H294' => ['Rimini', 'RN'],
        'H620' => ['Rovigo', 'RO'],
        'H703' => ['Salerno', 'SA'],
        'I726' => ['Siena', 'SI'],
        'I829' => ['Sondrio', 'SO'],
        'E463' => ['La Spezia', 'SP'],
        'I754' => ['Siracusa', 'SR'],
        'I452' => ['Sassari', 'SS'],
        'B745' => ['Carbonia', 'SU'],
        'I480' => ['Savona', 'SV'],
        'L049' => ['Taranto', 'TA'],
        'L103' => ['Teramo', 'TE'],
        'L378' => ['Trento', 'TN'],
        'L219' => ['Torino', 'TO'],
        'L331' => ['Trapani', 'TP'],
        'L117' => ['Terni', 'TR'],
        'L424' => ['Trieste', 'TS'],
        'L407' => ['Treviso', 'TV'],
        'L483' => ['Udine', 'UD'],
        'L682' => ['Varese', 'VA'],
        'L746' => ['Verbania', 'VB'],
        'L750' => ['Vercelli', 'VC'],
        'L736' => ['Venezia', 'VE'],
        'L840' => ['Vicenza', 'VI'],
        'L781' => ['Verona', 'VR'],
        'M082' => ['Viterbo', 'VT'],
        'F537' => ['Vibo Valentia', 'VV'],
    ];

    /** @var array<string, string> codice => stato */
    public const STATI_ESTERI = [
        'Z100' => 'Albania',
        'Z600' => 'Argentina',
        'Z249' => 'Bangladesh',
        'Z602' => 'Brasile',
        'Z210' => 'Cina Repubblica Popolare',
        'Z336' => 'Egitto',
        'Z216' => 'Filippine',
        'Z110' => 'Francia',
        'Z114' => 'Gran Bretagna e Irlanda del Nord',
        'Z222' => 'India',
        'Z330' => 'Marocco',
        'Z611' => 'Perù',
        'Z127' => 'Polonia',
        'Z129' => 'Romania',
        'Z131' => 'Spagna',
        'Z404' => 'Stati Uniti d\'America',
        'Z133' => 'Svizzera',
        'Z352' => 'Tunisia',
        'Z138' => 'Ucraina',
    ];

    /** Nome del luogo (comune o stato) per un codice catastale incluso in questo elenco, altrimenti null. */
    public static function name(string $code): ?string
    {
        $code = strtoupper($code);

        return self::CAPOLUOGHI[$code][0] ?? self::STATI_ESTERI[$code] ?? null;
    }
}
