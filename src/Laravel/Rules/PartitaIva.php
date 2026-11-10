<?php

declare(strict_types=1);

namespace GeneratoreDati\FakerItalia\Laravel\Rules;

use Closure;
use GeneratoreDati\FakerItalia\PartitaIva as Validator;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Regola di validazione Laravel: partita IVA italiana formalmente valida (accetta il prefisso "IT").
 *
 *     $request->validate(['piva' => ['required', new PartitaIva]]);
 */
final class PartitaIva implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! Validator::isValid($value)) {
            $fail('Il campo :attribute non è una partita IVA valida.');
        }
    }
}
