<?php

use App\Models\Admin;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Hash;
use Spatie\MediaLibrary\HasMedia;

/*
|--------------------------------------------------------------------------
| User model
|--------------------------------------------------------------------------
*/

test('the password is hashed automatically by the cast', function () {
    $user = User::factory()->create(['password' => 'plain-text-password']);

    expect($user->password)->not->toBe('plain-text-password')
        ->and(Hash::check('plain-text-password', $user->password))->toBeTrue();
});

test('the name is stored as a translation set', function () {
    $user = User::factory()->create([
        'name' => ['ar' => 'الاسم', 'en' => 'Name'],
    ]);

    expect($user->fresh()->getTranslations('name'))->toBe(['ar' => 'الاسم', 'en' => 'Name']);
});

test('the name resolves for the active locale', function (string $locale, string $expected) {
    $user = User::factory()->create([
        'name' => ['ar' => 'الاسم', 'en' => 'Name'],
    ]);

    app()->setLocale($locale);

    expect($user->fresh()->name)->toBe($expected);
})->with([
    ['ar', 'الاسم'],
    ['en', 'Name'],
]);

test('a plain string name is stored under the current locale', function () {
    $user = User::factory()->create(['name' => 'Plain Name']);

    expect($user->fresh()->getTranslations('name'))->toHaveKey(app()->getLocale());
});

test('sensitive attributes are hidden from serialisation', function () {
    $user = User::factory()->create()->fresh();

    $array = $user->toArray();

    // The #[Hidden] attribute on App\Models\User lists exactly these four.
    foreach (['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'] as $hidden) {
        expect($array)->not->toHaveKey($hidden);
    }

    expect($user->getHidden())->toBe([
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ]);
});

test('the active flag is cast to a boolean', function () {
    expect(User::factory()->create(['active' => true])->fresh()->active)->toBeTrue()
        ->and(User::factory()->create(['active' => false])->fresh()->active)->toBeFalse();
});

test('the used before flag is not cast to a boolean', function () {
    // BUG: App\Models\User::casts() omits used_before even though the column is a
    // boolean and Nationality casts the identically named column. The attribute
    // therefore comes back as int 1/0 rather than true/false, so strict
    // comparisons in the frontend and any `=== true` check silently fail.
    // See app/Models/User.php:53-61
    $user = User::factory()->create(['used_before' => true])->fresh();

    expect($user->getCasts())->not->toHaveKey('used_before')
        ->and($user->used_before)->toBe(1)
        ->and(User::factory()->create(['used_before' => false])->fresh()->used_before)->toBe(0);
});

test('the used before flag is still truthy and falsy as expected', function () {
    // The uncast value behaves correctly in boolean context, which is why the
    // missing cast has gone unnoticed so far.
    expect((bool) User::factory()->create(['used_before' => true])->fresh()->used_before)->toBeTrue()
        ->and((bool) User::factory()->create(['used_before' => false])->fresh()->used_before)->toBeFalse();
});

test('the active scope returns only active users', function () {
    User::factory()->create(['active' => true]);
    User::factory()->create(['active' => false]);

    expect(User::active()->count())->toBe(1)
        ->and(User::inActive()->count())->toBe(1);
});

test('the email verification timestamp is cast to a datetime', function () {
    $user = User::factory()->create();

    // AppServiceProvider binds CarbonImmutable, so date casts yield that class.
    expect($user->fresh()->email_verified_at)->toBeInstanceOf(CarbonImmutable::class);
});

test('deleting a user soft deletes it', function () {
    $user = User::factory()->create();

    $user->delete();

    expect(User::find($user->id))->toBeNull()
        ->and(User::withTrashed()->find($user->id))->not->toBeNull()
        ->and(User::withTrashed()->find($user->id)->trashed())->toBeTrue();
});

test('a soft deleted user is excluded from role lookups', function () {
    $user = userWithRoles(['Editor']);
    $user->delete();

    expect(User::count())->toBe(0)
        ->and(User::withTrashed()->count())->toBe(1);
});

test('the user exposes an admin profile polymorph', function () {
    $owner = User::factory()->create();

    $admin = Admin::create([
        'phone' => '01000000000',
        'added_by' => $owner->id,
    ]);

    $admin->user()->save(User::factory()->create());

    $user = User::whereHasMorph('profile', [Admin::class])->firstOrFail();

    expect($user->profile)->toBeInstanceOf(Admin::class)
        ->and($user->profile->phone)->toBe('01000000000');
});

test('a user without a profile has a null profile id', function () {
    expect(User::factory()->create()->profile_id)->toBeNull();
});

test('the model implements HasMedia and registers a default collection', function () {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(HasMedia::class)
        ->and($user->getRegisteredMediaCollections())->toHaveKey('default');
});

test('the default media collection only accepts image types', function () {
    $user = User::factory()->create();

    $collection = $user->getRegisteredMediaCollections()['default'];

    expect($collection->acceptsMimeTypes)->toContain('image/jpeg', 'image/png');
});

test('assigning a role to a user persists through the spatie pivot', function () {
    $role = createRole('Editor');
    $user = User::factory()->create();

    $user->assignRole($role);

    expect($user->fresh()->hasRole($role))->toBeTrue()
        ->and($user->fresh()->roles)->toHaveCount(1);
});

test('the super admin gate grants every ability', function () {
    $user = userWithRoles(['Super Admin']);

    expect($user->can('view admins'))->toBeTrue()
        ->and($user->can('delete role'))->toBeTrue()
        ->and($user->can('an ability that does not exist'))->toBeTrue();
});

test('a user without the super admin role is denied abilities', function () {
    $user = userWithRoles(['Editor']);

    // Gate::before returns null for non super admins, so the normal policy
    // check applies and fails for permissions the user does not hold.
    expect($user->can('an ability that does not exist'))->toBeFalse();
});

test('a user with an explicit permission is allowed that ability', function () {
    $user = actingAsUserWithPermissions('edit nationality');

    expect($user->can('edit nationality'))->toBeTrue()
        ->and($user->can('delete nationality'))->toBeFalse();
});
