<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AllowedEmailDomain implements Rule
{
    public function passes($attribute, $value)
    {
        $allowedDomains = env('ALLOWED_EMAIL');

        // If ALLOWED_EMAIL is not set, or is empty, allow any domain
        if (!$allowedDomains) {
            return true;
        }

        $allowedDomains = explode(',', $allowedDomains);

        foreach ($allowedDomains as $domain) {
            if (str_ends_with($value, $domain)) {
                return true;
            }
        }

        return false;
    }

    public function message()
    {
        return __('validation.email_domain.invalid');
    }
}
