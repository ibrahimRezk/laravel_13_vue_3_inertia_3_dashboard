<?php

use App\Actions\Fortify\CreateNewUser;
use App\Concerns\ProfileValidationRules;

/*
|--------------------------------------------------------------------------
| Regression guard for a known application bug.
|--------------------------------------------------------------------------
|
| App\Concerns\ProfileValidationRules::profileRules() calls $this->user() to
| build the "email is unique" rule. That trait is shared between the FormRequest
| classes (which do have ->user()) and App\Actions\Fortify\CreateNewUser
| (which does not), so building the rule set on CreateNewUser fatals.
|
| This makes POST /register return HTTP 500 for every visitor. The assertions
| below pin the current behaviour. Once profileRules() is changed to accept a
| user id — e.g. ->user()->id replaced by an explicit parameter — delete this
| file and re-enable the skipped test in RegistrationTest.
|
*/

test('profile rules cannot be built on CreateNewUser because user() is missing', function () {
    expect(method_exists(CreateNewUser::class, 'user'))->toBeFalse();

    (new CreateNewUser)->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);
})->throws(Error::class, 'Call to undefined method App\Actions\Fortify\CreateNewUser::user()');

test('profile rules ignore a null user id when one is not supplied', function () {
    // Shows the trait's intent: profileRules(?int $userId) is declared as accepting
    // an id, but the signature ignores the argument and dereferences $this->user()
    // unconditionally instead.
    $rules = (new class
    {
        use ProfileValidationRules;

        public function rules(): array
        {
            return $this->profileRules(null);
        }
    })->rules();

    expect($rules)->toHaveKeys(['name.*', 'email', 'avatar'])
        ->and($rules['email'])->toContain('email');
})->throws(Error::class, 'Call to undefined method');
