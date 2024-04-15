<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserExistsRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::with('userRole.rolePermissions')->where('id', '=', $value);
        if (!$user->exists()) {
            $fail("User id does $value not exist");
        } else if ($user->first()->userRole->role_name != 'Client') {
            $fail("User {$user->first()->email} is not a client.");
        }
    }
}
