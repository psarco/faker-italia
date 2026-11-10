<?php

declare(strict_types=1);

namespace GeneratoreDati\FakerItalia\Laravel\Rules;

use Closure;
use GeneratoreDati\FakerItalia\CodiceFiscale as Validator;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Regola di validazione Laravel: codice fiscale di persona fisica formalmente valido.
 *
 *     $request->validate(['cf' => ['required', new CodiceFiscale]]);
 */
final class CodiceFiscale implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! Validator::isValid($value)) {
            $fail('Il campo :attribute non è un codice fiscale valido.');
        }
    }
}
