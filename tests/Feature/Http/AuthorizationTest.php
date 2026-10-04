<?php

use App\Models\Nationality;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Authorization
|--------------------------------------------------------------------------
|
| Every controller below declares static middleware() with "can:" guards.
| Super admins bypass them via the Gate::before hook in AppServiceProvider;
| ordinary users must hold each permission explicitly.
|
*/

test('guests are redirected to login from the nationalities index', function () {
    $this->get(route('nationalities.index'))
        ->assertRedirect(route('login'));
});

test('guests are redirected to login from the roles index', function () {
    $this->get(route('roles.index'))
        ->assertRedirect(route('login'));
});

test('guests are redirected to login from the admins index', function () {
    $this->get(route('admins.index'))
        ->assertRedirect(route('login'));
});

test('guests are redirected to login from the system settings index', function () {
    $this->get(route('systemSettings.index'))
        ->assertRedirect(route('login'));
});

test('guests cannot create a nationality', function () {
    $this->post(route('nationalities.store'), [
        'name' => ['ar' => 'مصري', 'en' => 'Egyptian'],
        'active' => true,
    ])->assertRedirect(route('login'));

    expect(Nationality::count())->toBe(0);
});

test('a user without the view permission is forbidden on the nationalities index', function () {
    actingAsUserWithPermissions('create nationality');

    $this->get(route('nationalities.index'))->assertForbidden();
});

test('a user without the view permission is forbidden on the roles index', function () {
    actingAsUserWithPermissions('create role');

    $this->get(route('roles.index'))->assertForbidden();
});

test('a user without the create permission cannot create a nationality', function () {
    actingAsUserWithPermissions('view nationalities');

    $this->post(route('nationalities.store'), [
        'name' => ['ar' => 'مصري', 'en' => 'Egyptian'],
        'active' => true,
    ])->assertForbidden();

    expect(Nationality::count())->toBe(0);
});

test('a user without the delete permission cannot delete a nationality', function () {
    $actor = actingAsUserWithPermissions('view nationalities');
    $nationality = createNationality(['added_by' => $actor->id]);

    $this->delete(route('nationalities.destroy', $nationality->id))
        ->assertForbidden();

    expect(Nationality::find($nationality->id))->not->toBeNull();
});

test('a user without the edit permission cannot update a nationality', function () {
    $actor = actingAsUserWithPermissions('view nationalities');
    $nationality = createNationality(['added_by' => $actor->id]);

    $this->patch(route('nationalities.update', $nationality->id), [
        'name' => ['ar' => 'معدّل', 'en' => 'Updated'],
        'active' => false,
    ])->assertForbidden();
});

test('a user without the view permission is forbidden on the admins index', function () {
    actingAsUserWithPermissions('create admin');

    $this->get(route('admins.index'))->assertForbidden();
});

test('a user without the delete permission cannot delete a role', function () {
    actingAsUserWithPermissions('view roles');

    $role = createRole('Disposable');

    $this->delete(route('roles.destroy', $role))
        ->assertForbidden();

    expect(Role::find($role->id))->not->toBeNull();
});

test('a user without the edit permission cannot attach a permission to a role', function () {
    actingAsUserWithPermissions('view roles');

    $this->post(route('roles.attach-permission'), [
        'roleId' => createRole('Editor')->id,
        'permissionId' => createPermission('edit articles')->id,
        'type' => 1,
    ])->assertForbidden();
});

test('a user without the edit permission cannot detach a permission from a role', function () {
    actingAsUserWithPermissions('view roles');

    $this->post(route('roles.detach-permission'), [
        'roleId' => createRole('Editor')->id,
        'permissionId' => createPermission('edit articles')->id,
        'type' => 1,
    ])->assertForbidden();
});

test('a user without the upload permission cannot upload an image', function () {
    actingAsUserWithPermissions('view nationalities');

    $this->post(route('image.store'), [
        'file' => fakeUploadedImage(),
        'modelType' => 'user',
        'modelId' => User::factory()->create()->id,
    ])->assertForbidden();
});

test('a user without the edit permission cannot save system settings', function () {
    actingAsUserWithPermissions('view system settings');

    $this->post(route('systemSettings.store'), [
        'name' => ['ar' => 'شركة', 'en' => 'Company'],
        'address' => ['ar' => 'عنوان', 'en' => 'Address'],
        'active' => true,
        'phone' => '01000000000',
        'email' => 'info@example.com',
    ])->assertForbidden();

    expect(Setting::count())->toBe(0);
});

test('a user without the delete media permission cannot delete an image', function () {
    actingAsUserWithPermissions('upload media');

    $this->delete(route('image.destroy', 1))->assertForbidden();
});
