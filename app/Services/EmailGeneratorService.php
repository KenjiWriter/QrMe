<?php

namespace App\Services;

use Illuminate\Support\Str;

class EmailGeneratorService
{
    /**
     * Generate an email in the form: {first_letter}.{lastname}@{APP_DOMAIN}
     * e.g. "Maciej", "Wiśniewski" -> "m.wisniewski@reganta.pl"
     */
    public function generate(string $firstName, string $lastName): string
    {
        $firstName = trim($firstName);
        $lastName = trim($lastName);

        if ($firstName === '' || $lastName === '') {
            return '';
        }

        $firstLetter = mb_strtolower(Str::ascii(mb_substr($firstName, 0, 1)));
        $lastSlug = Str::slug(Str::ascii($lastName), '');
        $domain = config('app.domain', env('APP_DOMAIN', 'example.pl'));

        return sprintf('%s.%s@%s', $firstLetter, $lastSlug, $domain);
    }
}
