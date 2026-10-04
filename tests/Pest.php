<?php

use App\Models\Nationality;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, it may not be enough for some of your testing
| needs. Here are some testing helpers that you may use in your tests.
|
*/

/**
 * The application grants every ability to this role via the Gate::before hook
 * in AppServiceProvider, so no explicit permissions are needed.
 */
const SUPER_ADMIN_ROLE = 'Super Admin';

/**
 * Create a user and assign it the given roles.
 *
 * @param  array<int, string>|string  $roles
 */
function userWithRoles(array|string $roles = SUPER_ADMIN_ROLE, array $attributes = []): User
{
    $user = User::factory()->create($attributes);

    foreach ((array) $roles as $role) {
        $user->assignRole(createRole($role));
    }

    return $user->fresh();
}

/**
 * Create a super admin and authenticate as them.
 */
function actingAsSuperAdmin(array $attributes = []): User
{
    $user = userWithRoles(SUPER_ADMIN_ROLE, $attributes);

    test()->actingAs($user);

    return $user;
}

/**
 * Create a user with only the given permissions, and authenticate as them.
 *
 * Useful for exercising the "can:" middleware against a non-super-admin.
 *
 * @param  array<int, string>|string  $permissions
 */
function actingAsUserWithPermissions(array|string $permissions, array $attributes = []): User
{
    $user = User::factory()->create($attributes);

    $user->givePermissionTo(createPermissions($permissions));

    test()->actingAs($user);

    return $user->fresh();
}

/**
 * Create a role, reusing an existing one when the name matches.
 */
function createRole(string $name, array $attributes = []): Role
{
    return Role::firstOrCreate(
        ['name' => $name, 'guard_name' => 'web'],
        array_merge([
            'slug' => [
                'ar' => $name,
                'en' => $name,
            ],
        ], $attributes)
    );
}

/**
 * Create permissions, reusing existing ones when the names match.
 *
 * @param  array<int, string>|string  $permissions
 * @return Collection<int, Permission>
 */
function createPermissions(array|string $permissions): Collection
{
    return collect((array) $permissions)->map(fn (string $name) => createPermission($name));
}

/**
 * Create a single permission, reusing an existing one when the name matches.
 */
function createPermission(string $name, string $guard = 'web'): Permission
{
    return Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
}

/**
 * Create a nationality with the given translations.
 *
 * @param  array<string, string>  $name
 */
function createNationality(array $name = [], array $attributes = []): Nationality
{
    $defaults = [
        'name' => [
            'ar' => $name['ar'] ?? 'مصري',
            'en' => $name['en'] ?? 'Egyptian',
        ],
    ];

    // Only fill in name.* when the caller has not supplied its own name array,
    // so a full override such as ['name' => ['en' => 'X']] is respected.
    foreach (['ar', 'en'] as $locale) {
        if (isset($name[$locale])) {
            $defaults['name'][$locale] = $name[$locale];
        }
    }

    if (array_key_exists('name', $attributes)) {
        unset($defaults['name']);
    }

    return Nationality::factory()->create(array_merge($defaults, $attributes));
}

/**
 * Build a fake uploaded image for upload endpoints.
 */
function fakeUploadedImage(string $name = 'avatar.jpg'): UploadedFile
{
    return UploadedFile::fake()->image($name);
}
