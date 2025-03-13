<?php

namespace App\Rules;

use Closure;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailOrId implements ValidationRule
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
        return filter_var($value, FILTER_VALIDATE_EMAIL) && User::where('email', $value)->exists()
            || is_numeric($value) && User::where('id', $value)->exists();
    }

    public function message()
    {
        return 'The :attribute must be a valid user email or user id.';
    }
}
