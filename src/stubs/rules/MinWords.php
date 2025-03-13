<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MinWords implements ValidationRule
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

    protected $minWords;

    public function __construct($minWords)
    {
        $this->minWords = $minWords;
    }

    public function passes($attribute, $value)
    {
        // Count the number of words in the value
        return str_word_count($value) >= $this->minWords;
    }

    public function message()
    {
        return "The :attribute must have at least {$this->minWords} words.";
    }
}
