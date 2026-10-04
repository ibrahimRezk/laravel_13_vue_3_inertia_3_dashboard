<?php

use App\Http\Resources\AdminResource;
use App\Http\Resources\PagePermissionResource;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\SettingResource;
use App\Http\Resources\UserResource;
use App\Models\Admin;
use App\Models\PagePermission;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Models\Permission;

/*
|--------------------------------------------------------------------------
| Resource edge cases
|--------------------------------------------------------------------------
|
| These pin behaviours that are reachable but fragile: two resources fatal for
| an unauthenticated caller, and one emits a mistyped key. Each assertion names
| the file and line so the test can be deleted when the bug is fixed.
|
*/

test('admin resource serialises for an authenticated user', function () {
    actingAsSuperAdmin();
    request()->setUserResolver(fn () => auth()->user());

    $data = (new AdminResource(new Admin(['phone' => '01000000000'])))->toArray(request());

    expect($data)->toHaveKeys(['id', 'phone', 'can'])
        ->and($data['can'])->toHaveKeys(['edit', 'delete']);
});

test('admin resource fatals for a guest', function () {
    // BUG: AdminResource calls auth()->user()->can() with no null-safe operator,
    // so serialising it without an authenticated user throws
    // "Call to a member function can() on null". The model is not null-safe
    // either, so this cannot be reached safely from any guest-facing path.
    // See app/Http/Resources/AdminResource.php:20
    expect(fn () => (new AdminResource(new Admin(['phone' => '010'])))->toArray(Request::create('/')))
        ->toThrow(Error::class, 'Call to a member function can() on null');
});

test('setting resource fatals for a guest', function () {
    // BUG: SettingResource uses $request->user()->can() rather than
    // $request->user()?->can(), so it throws for an unauthenticated request.
    // Every sibling resource already uses the null-safe form.
    // See app/Http/Resources/SettingResource.php:58
    $setting = new Setting([
        'name' => ['ar' => 'ش', 'en' => 'Company'],
        'address' => ['ar' => 'ع', 'en' => 'Address'],
        'email' => 'info@example.com',
        'phone' => '01000000000',
        'active' => true,
    ]);

    expect(fn () => (new SettingResource($setting))->toArray(Request::create('/')))
        ->toThrow(Error::class, 'Call to a member function can() on null');
});

test('setting resource serialises for an authenticated user', function () {
    actingAsSuperAdmin();
    request()->setUserResolver(fn () => auth()->user());

    $setting = Setting::create([
        'name' => ['ar' => 'شركة', 'en' => 'Company'],
        'address' => ['ar' => 'عنوان', 'en' => 'Address'],
        'phone' => '01000000000',
        'email' => 'info@example.com',
        'added_by' => auth()->id(),
    ]);

    $data = (new SettingResource($setting->fresh()))->toArray(request());

    expect($data)->toHaveKey('can')
        ->and($data['can']['edit'])->toBeTrue();
});

test('page permission resource emits a type key with a trailing space', function () {
    // BUG: the array key is written as 'type ' with a trailing space, so the
    // payload contains "type " instead of "type" and any frontend reading
    // item.type sees undefined. Every other resource uses a clean key.
    // See app/Http/Resources/PagePermissionResource.php:24
    $permission = PagePermission::create([
        'name' => ['ar' => 'الأدوار', 'en' => 'roles'],
        'permissions' => ['view' => 1],
        'type' => 1,
    ]);

    $data = (new PagePermissionResource($permission->fresh()))->toArray(request());

    expect($data)->toHaveKey('type ')
        ->and($data)->not->toHaveKey('type');
});

test('page permission resource exposes translations and permissions', function () {
    $permission = PagePermission::create([
        'name' => ['ar' => 'الأدوار', 'en' => 'roles'],
        'permissions' => ['view' => 1, 'create' => 2],
        'type' => 1,
    ]);

    $data = (new PagePermissionResource($permission->fresh()))->toArray(request());

    expect($data)->toHaveKeys(['id', 'name', 'name.ar', 'name.en', 'permissions'])
        ->and($data['name.en'])->toBe('roles')
        ->and($data['permissions'])->toBe(['view' => 1, 'create' => 2]);
});

test('the profile branch is not serialised inline by UserResource', function () {
    // BUG: UserResource::toArray() returns "new AdminResource(...)" from inside
    // a when() callback. The enclosing toArray() therefore hands back an
    // unresolved JsonResource, not a nested array, so callers inspecting
    // $data['profile'] get a resource object rather than the array the
    // 'profile' shape implies. Serialising it needs an explicit second call.
    // See app/Http/Resources/UserResource.php:44
    actingAsSuperAdmin();
    request()->setUserResolver(fn () => auth()->user());

    $owner = User::factory()->create();
    $admin = Admin::create(['phone' => '01000000000', 'added_by' => $owner->id]);
    $subject = User::factory()->create();
    $admin->user()->save($subject);

    $data = (new UserResource($subject->fresh()))->toArray(request());

    expect($data['profile'])->toBeInstanceOf(JsonResource::class)
        ->and($data['profile'])->not->toBeArray();
});

test('permission resource reports abilities for the current user', function () {
    actingAsSuperAdmin();
    request()->setUserResolver(fn () => auth()->user());

    $media = new Media(['id' => 1, 'name' => 'edit permission']);

    // PermissionResource only needs the name and timestamps to serialise.
    $permission = new Permission(['name' => 'edit permission']);
    $permission->id = 1;

    $data = (new PermissionResource($permission))->toArray(request());

    expect($data)->toHaveKeys(['id', 'name', 'can'])
        ->and($data['name'])->toBe('edit permission');
});

test('user resource omits the profile branch for a user without one', function () {
    actingAsSuperAdmin();
    request()->setUserResolver(fn () => auth()->user());

    $user = User::factory()->create();

    $data = (new UserResource($user))->toArray(request());

    // The profile key is wrapped in when($this->profile_id), so a user with no
    // morph profile yields a MissingValue rather than a nested resource.
    expect($data['profile'])->toBeInstanceOf(MissingValue::class);
});

test('user resource nests an admin resource for a user with a profile', function () {
    actingAsSuperAdmin();
    request()->setUserResolver(fn () => auth()->user());

    $owner = User::factory()->create();
    $admin = Admin::create(['phone' => '01000000000', 'added_by' => $owner->id]);

    $subject = User::factory()->create();
    $admin->user()->save($subject);

    $data = (new UserResource($subject->fresh()))->toArray(request());

    // The profile branch returns an AdminResource instance rather than a nested
    // array, so it has to be resolved explicitly. It is also resolved against
    // the auth() facade inside AdminResource, not the injected request.
    expect($data['profile'])->toBeInstanceOf(AdminResource::class);

    $profile = $data['profile']->toArray(request());

    expect($profile)->toHaveKeys(['id', 'phone', 'can'])
        ->and($profile['phone'])->toBe('01000000000');
});
