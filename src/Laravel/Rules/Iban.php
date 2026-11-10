<?php

declare(strict_types=1);

namespace GeneratoreDati\FakerItalia\Laravel\Rules;

use Closure;
use GeneratoreDati\FakerItalia\Iban as Validator;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Regola di validazione Laravel: IBAN valido, con controllo del CIN per gli IBAN italiani.
 *
 *     $request->validate(['iban' => ['required', new Iban]]);              // qualsiasi paese
 *     $request->validate(['iban' => ['required', new Iban(onlyItalian: true)]]);
 */
final class Iban implements ValidationRule
{
    public function __construct(private readonly bool $onlyItalian = false)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('Il campo :attribute non è un IBAN valido.');

            return;
        }

        $result = Validator::validate($value);
        if ($result['status'] !== 'valid') {
            $fail('Il campo :attribute non è un IBAN valido.');
        } elseif ($this->onlyItalian && $result['country'] !== 'IT') {
            $fail('Il campo :attribute deve essere un IBAN italiano.');
        }
    }
}
