<?php

use App\Models\PagePermission;
use App\Models\User;
use App\QueryBuilders\AdminUserQueryBuilder;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| AdminUserQueryBuilder
|--------------------------------------------------------------------------
|
| The builder excludes the authenticated user, so every test authenticates
| first: auth()->id() is null for a guest and the where clause would then
| compare against null.
|
*/

function adminUserRequest(array $query = []): Request
{
    return Request::create('/admins?'.http_build_query($query), 'GET');
}

test('it excludes the authenticated user from the results', function () {
    $admin = actingAsSuperAdmin();
    $other = User::factory()->create();

    $ids = AdminUserQueryBuilder::forRequest(adminUserRequest())->pluck('id');

    expect($ids)->toContain($other->id)
        ->and($ids)->not->toContain($admin->id);
});

test('it returns other users in descending id order', function () {
    actingAsSuperAdmin();

    $first = User::factory()->create();
    $second = User::factory()->create();
    $third = User::factory()->create();

    $ids = AdminUserQueryBuilder::forRequest(adminUserRequest())->pluck('id');

    expect($ids->all())->toBe([$third->id, $second->id, $first->id]);
});

test('it filters by the english name', function () {
    actingAsSuperAdmin();

    $match = User::factory()->create(['name' => ['ar' => 'محرر', 'en' => 'Editor One']]);
    User::factory()->create(['name' => ['ar' => 'مراجع', 'en' => 'Reviewer Two']]);

    $results = AdminUserQueryBuilder::forRequest(adminUserRequest(['name' => 'Editor']))->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($match->id);
});

test('it filters by the arabic name', function () {
    actingAsSuperAdmin();

    User::factory()->create(['name' => ['ar' => 'محرر', 'en' => 'Editor One']]);
    $match = User::factory()->create(['name' => ['ar' => 'مراجع', 'en' => 'Reviewer Two']]);

    $results = AdminUserQueryBuilder::forRequest(adminUserRequest(['name' => 'مراجع']))->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($match->id);
});

test('it filters to active users', function () {
    actingAsSuperAdmin();

    $active = User::factory()->create(['active' => true]);
    User::factory()->create(['active' => false]);

    $ids = AdminUserQueryBuilder::forRequest(adminUserRequest(['active' => '1']))->pluck('id');

    expect($ids)->toContain($active->id)
        ->and($ids)->toHaveCount(1);
});

test('it filters to inactive users', function () {
    actingAsSuperAdmin();

    User::factory()->create(['active' => true]);
    $inactive = User::factory()->create(['active' => false]);

    $ids = AdminUserQueryBuilder::forRequest(adminUserRequest(['active' => '0']))->pluck('id');

    expect($ids)->toContain($inactive->id)
        ->and($ids)->toHaveCount(1);
});

test('an empty name filter is ignored', function () {
    actingAsSuperAdmin();

    User::factory()->count(2)->create();

    expect(AdminUserQueryBuilder::forRequest(adminUserRequest(['name' => '']))->get())->toHaveCount(2);
});

test('it eager loads the roles and media relations', function () {
    actingAsSuperAdmin();

    $user = User::factory()->create();

    $result = AdminUserQueryBuilder::forRequest(adminUserRequest())->first();

    expect($result->relationLoaded('roles'))->toBeTrue()
        ->and($result->relationLoaded('media'))->toBeTrue();
});

test('it selects only the columns the index needs', function () {
    actingAsSuperAdmin();

    $user = User::factory()->create();

    $result = AdminUserQueryBuilder::forRequest(adminUserRequest())->first();

    expect($result->email)->not->toBeNull()
        // avatar is deliberately omitted from the select list.
        ->and($result->getAttributes())->not->toHaveKey('avatar');
});

test('soft deleted users are excluded', function () {
    actingAsSuperAdmin();

    $user = User::factory()->create();
    $user->delete();

    $ids = AdminUserQueryBuilder::forRequest(adminUserRequest())->pluck('id');

    expect($ids)->not->toContain($user->id);
});

test('it paginates the way the index consumes it', function () {
    actingAsSuperAdmin();

    User::factory()->count(15)->create();

    $paginator = AdminUserQueryBuilder::forRequest(adminUserRequest())
        ->paginate(10)
        ->onEachSide(1)
        ->appends(adminUserRequest()->query());

    expect($paginator->total())->toBe(15)
        ->and($paginator->perPage())->toBe(10)
        ->and($paginator->lastPage())->toBe(2);
});

/*
|--------------------------------------------------------------------------
| PagePermission
|--------------------------------------------------------------------------
*/

test('page permissions round-trip their json columns', function () {
    $permission = PagePermission::create([
        'name' => ['ar' => 'الأدوار', 'en' => 'roles'],
        'permissions' => ['view' => 1, 'create' => 2],
        'type' => 1,
    ]);

    $fresh = $permission->fresh();

    expect($fresh->getTranslations('name'))->toBe(['ar' => 'الأدوار', 'en' => 'roles'])
        ->and($fresh->permissions)->toBe(['view' => 1, 'create' => 2])
        ->and($fresh->type)->toBe(1);
});

test('page permissions store the permission list as json', function () {
    $permission = PagePermission::create([
        'name' => ['ar' => 'الأدوار', 'en' => 'roles'],
        'permissions' => ['view' => 1],
        'type' => 1,
    ]);

    expect(json_decode($permission->fresh()->getRawOriginal('permissions'), true))
        ->toBe(['view' => 1]);
});

test('page permissions default to the normal type', function () {
    $permission = PagePermission::create([
        'name' => ['ar' => 'الأدوار', 'en' => 'roles'],
        'permissions' => ['view' => 1],
    ]);

    expect($permission->fresh()->type)->toBe(1);
});

test('special page permissions are flagged with type two', function () {
    $permission = PagePermission::create([
        'name' => ['ar' => 'إعدادات', 'en' => 'view system settings'],
        'permissions' => ['id' => 1],
        'type' => 2,
    ]);

    expect($permission->fresh()->type)->toBe(2);
});

test('the roles edit page lists only normal page permissions', function () {
    actingAsSuperAdmin();

    PagePermission::create([
        'name' => ['ar' => 'الأدوار', 'en' => 'roles'],
        'permissions' => ['view' => 1],
        'type' => 1,
    ]);
    PagePermission::create([
        'name' => ['ar' => 'إعدادات', 'en' => 'view system settings'],
        'permissions' => ['id' => 2],
        'type' => 2,
    ]);

    $role = createRole('Editor', ['slug' => ['ar' => 'محرر', 'en' => 'Editor']]);

    $this->get(route('roles.edit', $role))
        ->assertInertia(function ($page) {
            $props = $page->toArray()['props'];

            $normal = array_column($props['pagesPermissions'], 'name.en');
            $special = array_column($props['specialPermissions'], 'name.en');

            expect($normal)->toBe(['roles'])
                ->and($special)->toBe(['view system settings']);
        });
});
