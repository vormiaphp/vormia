<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailOrPhone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //
    }

    public function passes($attribute, $value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL)
            || preg_match('/^(\+\d{1,3}[- ]?)?\d{10}$/', $value);
    }

    public function message()
    {
        return 'The :attribute must be a valid email address or phone number.';
    }
}
