<?php

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Unique;

/*
|--------------------------------------------------------------------------
| Shared validation rule traits
|--------------------------------------------------------------------------
|
| Both traits expose protected methods, so they are exercised through a small
| anonymous host class rather than a FormRequest.
|
*/

/**
 * Host exposing the protected rule builders for direct inspection.
 */
function profileRuleHost(): object
{
    return new class
    {
        use PasswordValidationRules, ProfileValidationRules;

        public function profile(?int $userId = null): array
        {
            return $this->profileRules($userId);
        }

        public function password(): array
        {
            return $this->passwordRules();
        }

        public function currentPassword(): array
        {
            return $this->currentPasswordRules();
        }

        public function name(): array
        {
            return $this->nameRules();
        }

        public function email(?int $userId = null): array
        {
            return $this->emailRules($userId);
        }

        /**
         * Stand-in for FormRequest::user(), which ProfileValidationRules relies on.
         */
        public ?User $actingAs = null;

        public function user(): ?User
        {
            return $this->actingAs;
        }
    };
}

test('password rules require confirmation', function () {
    expect(profileRuleHost()->password())->toContain('required', 'string', 'confirmed');
});

test('the password slot uses the framework default policy outside production', function () {
    // AppServiceProvider returns null from its Password::defaults() callback when
    // not in production, so Laravel falls back to its own default rather than
    // disabling the rule. The rule object is still present and still enforces a
    // minimum length of 8 characters.
    //
    // Password::default() constructs a fresh instance per call, so identity
    // comparison is not usable here; the behaviour is asserted instead by the
    // two tests below.
    $rules = profileRuleHost()->password();

    $passwordRules = array_values(array_filter(
        $rules,
        fn ($rule) => $rule instanceof Password
    ));

    expect($passwordRules)->toHaveCount(1)
        ->and(Password::default())->toBeInstanceOf(Password::class);
});

test('a password shorter than eight characters is rejected', function () {
    $validator = Validator::make(
        ['password' => 'abc', 'password_confirmation' => 'abc'],
        ['password' => profileRuleHost()->password()]
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->get('password')[0])->toContain('at least 8 characters');
});

test('an eight character password is accepted outside production', function () {
    // No mixedCase/letters/numbers/symbols/uncompromised requirements are added
    // outside production, so a plain word clears validation.
    $validator = Validator::make(
        ['password' => 'password', 'password_confirmation' => 'password'],
        ['password' => profileRuleHost()->password()]
    );

    expect($validator->fails())->toBeFalse();
});

test('a mismatched confirmation is rejected', function () {
    $validator = Validator::make(
        ['password' => 'abc', 'password_confirmation' => 'different'],
        ['password' => profileRuleHost()->password()]
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('password'))->toBeTrue();
});

test('current password rules require the current_password rule', function () {
    expect(profileRuleHost()->currentPassword())
        ->toBe(['required', 'string', 'current_password']);
});

test('name rules require a string up to 255 characters', function () {
    expect(profileRuleHost()->name())->toBe(['required', 'string', 'max:255']);
});

test('email rules enforce a unique user when no id is supplied', function () {
    $rules = profileRuleHost()->email(null);

    expect($rules)->toContain('required', 'string', 'email', 'max:255')
        ->and($rules[count($rules) - 1])->toBeInstanceOf(Unique::class);
});

test('email rules reject an address already taken by another user', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $validator = Validator::make(
        ['email' => 'taken@example.com'],
        ['email' => profileRuleHost()->email(null)]
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('email'))->toBeTrue();
});

test('email rules allow a user to keep their own address', function () {
    $user = User::factory()->create(['email' => 'mine@example.com']);

    $validator = Validator::make(
        ['email' => 'mine@example.com'],
        ['email' => profileRuleHost()->email($user->id)]
    );

    expect($validator->fails())->toBeFalse();
});

test('profile rules key the name field with a wildcard', function () {
    // BUG: "name.*" only matches an array of translations. A plain string name
    // passes validation with no errors but is omitted from validated(), so the
    // field is silently discarded on save. See app/Concerns/ProfileValidationRules.php:20
    $host = profileRuleHost();
    $host->actingAs = User::factory()->create();

    $rules = $host->profile();

    expect($rules)->toHaveKey('name.*')
        ->and($rules)->not->toHaveKey('name');
});

test('a string name passes profile validation but is omitted from validated data', function () {
    $host = profileRuleHost();
    $host->actingAs = User::factory()->create();

    $rules = $host->profile();

    $validator = Validator::make(
        ['name' => 'Test User', 'email' => 'test@example.com'],
        $rules
    );

    expect($validator->fails())->toBeFalse()
        ->and($validator->validated())->toHaveKey('email')
        ->and($validator->validated())->not->toHaveKey('name');
});

test('a translated name array is retained by validated data', function () {
    $host = profileRuleHost();
    $host->actingAs = User::factory()->create();

    $rules = $host->profile();

    $validator = Validator::make(
        ['name' => ['ar' => 'اختبار', 'en' => 'Test User'], 'email' => 'test@example.com'],
        $rules
    );

    expect($validator->fails())->toBeFalse()
        ->and($validator->validated())->toHaveKey('name')
        ->and($validator->validated()['name'])->toBe(['ar' => 'اختبار', 'en' => 'Test User']);
});

test('avatar rules only apply when a file is present', function () {
    $host = profileRuleHost();
    $host->actingAs = User::factory()->create();

    expect($host->profile()['avatar'])->toBe(['sometimes', 'image', 'max:1024']);
});

test('profileRules ignores its own user id argument and always reads $this->user()', function () {
    // BUG: the signature accepts ?int $userId but never uses it; line 27 calls
    // $this->user()->id unconditionally, so it fatals for any host without a
    // resolvable user (notably App\Actions\Fortify\CreateNewUser).
    // See app/Concerns/ProfileValidationRules.php:15 and :27
    $host = profileRuleHost();

    expect(fn () => $host->profile(42))
        ->toThrow(ErrorException::class, 'Attempt to read property "id" on null');
});

test('profileRules succeeds once the host can resolve a user', function () {
    $host = profileRuleHost();
    $host->actingAs = User::factory()->create(['email' => 'mine@example.com']);

    // The ignored id comes from the resolved user rather than the argument
    // passed to profileRules(), so the resolved user's own address validates.
    $validator = Validator::make(
        ['name' => ['en' => 'Test User'], 'email' => 'mine@example.com'],
        $host->profile(null)
    );

    expect($validator->fails())->toBeFalse();
});
