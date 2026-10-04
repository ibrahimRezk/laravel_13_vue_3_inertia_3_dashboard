<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

/*
|--------------------------------------------------------------------------
| Seeders
|--------------------------------------------------------------------------
*/

test('UserSeeder creates a single super admin', function () {
    (new UserSeeder)->run();

    expect(User::count())->toBe(1)
        ->and(User::min('id'))->toBe(1)
        ->and(User::first()->email)->toBe('admin@gmail.com');
});

test('RolesSeeder creates the super admin role', function () {
    (new UserSeeder)->run();
    (new RolesSeeder)->run();

    expect(Role::where('name', 'Super Admin')->exists())->toBeTrue()
        ->and(Role::count())->toBe(1);
});

test('RolesSeeder assigns the super admin role to the first user', function () {
    (new UserSeeder)->run();
    (new RolesSeeder)->run();

    expect(User::find(1)->hasRole('Super Admin'))->toBeTrue();
});

test('RolesSeeder creates permissions for every managed page', function () {
    (new UserSeeder)->run();
    (new RolesSeeder)->run();

    $names = Permission::pluck('name');

    foreach ([
        'view nationalities', 'create nationality', 'edit nationality', 'delete nationality',
        'view roles', 'create role', 'edit role', 'delete role',
        'view admins', 'create admin', 'edit admin', 'delete admin',
        'view users', 'create user', 'edit user', 'delete user',
        'view system settings', 'edit system settings',
        'upload media', 'delete media',
    ] as $expected) {
        expect($names)->toContain($expected);
    }
});

test('RolesSeeder creates a page permission entry per managed page', function () {
    (new UserSeeder)->run();
    (new RolesSeeder)->run();

    $pages = DB::table('page_permissions')->get();

    $normal = $pages->filter(fn ($p) => $p->type === 1)->pluck('name');
    $special = $pages->filter(fn ($p) => $p->type === 2)->pluck('name');

    expect($normal)->toHaveCount(4)
        ->and($special)->toHaveCount(4);
});

test('RolesSeeder is safe to run twice', function () {
    (new UserSeeder)->run();
    (new RolesSeeder)->run();

    // The seeder truncates its tables first, so a second pass must not
    // accumulate duplicate roles or permissions.
    (new RolesSeeder)->run();

    expect(Role::count())->toBe(1)
        ->and(Permission::count())->toBe(20);
});

test('RolesSeeder fatals when no user with id 1 exists', function () {
    // BUG: RolesSeeder hard-codes User::find(1)->assignRole(...) with no null
    // check, so seeding any database that does not already contain a user whose
    // primary key is 1 aborts with "Call to a member function assignRole() on null".
    // That makes `php artisan db:seed` fail on a fresh install unless UserSeeder
    // happened to run first and produce id 1.
    // See database/seeders/RolesSeeder.php:48
    expect(User::count())->toBe(0);

    expect(fn () => (new RolesSeeder)->run())
        ->toThrow(Error::class, 'Call to a member function assignRole() on null');
});

test('DatabaseSeeder fails on a fresh database', function () {
    // BUG: DatabaseSeeder calls UserSeeder then RolesSeeder, and UserSeeder relies
    // on the autoincrement starting at 1. After any prior insert/delete cycle
    // the next user gets a higher id, so RolesSeeder's User::find(1) lookup
    // returns null and the whole seed aborts partway through, leaving the
    // nationalities table empty.
    // See database/seeders/DatabaseSeeder.php and RolesSeeder.php:48
    User::factory()->create();
    User::factory()->create();
    User::query()->delete();
    User::factory()->create();

    // A user now exists but its id is not 1.
    expect(User::min('id'))->not->toBe(1);

    expect(fn () => (new DatabaseSeeder)->run())
        ->toThrow(Error::class, 'Call to a member function assignRole() on null');
});

test('DatabaseSeeder populates nationalities when the ids line up', function () {
    // DatabaseSeeder already calls UserSeeder, so it must not be run separately:
    // UserSeeder inserts a fixed email and is not idempotent.
    (new DatabaseSeeder)->run();

    expect(Role::where('name', 'Super Admin')->exists())->toBeTrue()
        ->and(DB::table('nationalities')->count())->toBe(100)
        ->and(DB::table('page_permissions')->count())->toBe(8);
});

test('seeded nationalities all reference a real user', function () {
    (new DatabaseSeeder)->run();

    $orphans = DB::table('nationalities')
        ->whereNotIn('added_by', DB::table('users')->pluck('id'))
        ->count();

    // The factory resolves added_by to a real user, so no row is orphaned.
    expect($orphans)->toBe(0);
});
