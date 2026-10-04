<?php

use App\Actions\Role\CreateRoleAction;
use App\Actions\Role\DeleteRoleAction;
use App\Actions\Role\RolePermissionAction;
use App\Actions\Role\UpdateRoleAction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpKernel\Exception\HttpException;

/*
|--------------------------------------------------------------------------
| Role actions
|--------------------------------------------------------------------------
*/

test('creating a role stores the name, both slug translations and the web guard', function () {
    $role = (new CreateRoleAction)->execute([
        'name' => 'Editor',
        'slug' => ['ar' => 'محرر', 'en' => 'Editor'],
    ]);

    expect($role->exists)->toBeTrue()
        ->and($role->name)->toBe('Editor')
        ->and($role->guard_name)->toBe('web')
        ->and($role->getTranslation('slug', 'ar'))->toBe('محرر')
        ->and($role->getTranslation('slug', 'en'))->toBe('Editor')
        // The migration default is 0, but the freshly created in-memory model
        // reports null until reloaded.
        ->and($role->fresh()->used_before)->toBeFalse();
});

test('a created role can be found again with its translations intact', function () {
    (new CreateRoleAction)->execute([
        'name' => 'Reviewer',
        'slug' => ['ar' => 'مراجع', 'en' => 'Reviewer'],
    ]);

    $role = Role::where('name', 'Reviewer')->firstOrFail();

    expect($role->getTranslations('slug'))->toBe(['ar' => 'مراجع', 'en' => 'Reviewer']);
});

test('updating a role changes the name and both slug translations', function () {
    $role = (new CreateRoleAction)->execute([
        'name' => 'Editor',
        'slug' => ['ar' => 'محرر', 'en' => 'Editor'],
    ]);

    $updated = (new UpdateRoleAction)->execute($role, [
        'name' => 'Editor',
        'slug' => ['ar' => 'محرر جديد', 'en' => 'Senior Editor'],
    ]);

    expect($updated->fresh()->name)->toBe('Editor')
        ->and($updated->fresh()->getTranslation('slug', 'ar'))->toBe('محرر جديد')
        ->and($updated->fresh()->getTranslation('slug', 'en'))->toBe('Senior Editor');
});

test('updating a role leaves the guard untouched', function () {
    $role = (new CreateRoleAction)->execute([
        'name' => 'Editor',
        'slug' => ['ar' => 'محرر', 'en' => 'Editor'],
    ]);

    $updated = (new UpdateRoleAction)->execute($role, [
        'name' => 'Editor',
        'slug' => ['ar' => 'محرر', 'en' => 'Editor Two'],
    ]);

    expect($updated->fresh()->guard_name)->toBe('web');
});

test('deleting an unused role removes it', function () {
    // The first role created lands on the reserved id 1 and cannot be deleted,
    // so occupy it and delete the second.
    (new CreateRoleAction)->execute([
        'name' => 'Reserved First',
        'slug' => ['ar' => 'محجوز', 'en' => 'Reserved First'],
    ]);

    $role = (new CreateRoleAction)->execute([
        'name' => 'Disposable',
        'slug' => ['ar' => 'مؤقت', 'en' => 'Disposable'],
    ]);

    (new DeleteRoleAction)->execute($role);

    expect(Role::find($role->id))->toBeNull();
});

test('deleting the role with id 1 is forbidden', function () {
    // CreateRoleAction inserts in order, so the first role created in a test
    // lands on the reserved id 1 that DeleteRoleAction protects.
    $role = (new CreateRoleAction)->execute([
        'name' => 'Reserved First',
        'slug' => ['ar' => 'محجوز', 'en' => 'Reserved First'],
    ]);

    expect($role->id)->toBe(1);

    expect(fn () => (new DeleteRoleAction)->execute($role->fresh()))
        ->toThrow(HttpException::class, 'general.can_not_delete_super_admin_role');

    expect(Role::find($role->id))->not->toBeNull();
});

test('deleting a role that has been used before is forbidden', function () {
    // Occupy id 1 first so the subject of this test is not the reserved role.
    (new CreateRoleAction)->execute([
        'name' => 'Reserved First',
        'slug' => ['ar' => 'محجوز', 'en' => 'Reserved First'],
    ]);

    $role = (new CreateRoleAction)->execute([
        'name' => 'Historic',
        'slug' => ['ar' => 'قديم', 'en' => 'Historic'],
    ]);
    $role->forceFill(['used_before' => true])->save();

    expect($role->id)->not->toBe(1);

    expect(fn () => (new DeleteRoleAction)->execute($role->fresh()))
        ->toThrow(HttpException::class, 'general.item_has_previous_activity_or_no_permission');

    expect(Role::find($role->id))->not->toBeNull();
});

/*
|--------------------------------------------------------------------------
| RolePermissionAction
|--------------------------------------------------------------------------
*/

test('attaching a permission to a role grants it', function () {
    $role = createRole('Editor');
    $permission = createPermission('edit articles');

    (new RolePermissionAction)->attach($role->id, $permission->id, 1);

    expect($role->fresh()->hasPermissionTo($permission))->toBeTrue();
});

test('detaching a permission from a role revokes it', function () {
    $role = createRole('Editor');
    $permission = createPermission('edit articles');

    (new RolePermissionAction)->attach($role->id, $permission->id, 1);
    (new RolePermissionAction)->detach($role->id, $permission->id, 1);

    expect($role->fresh()->hasPermissionTo($permission))->toBeFalse();
});

test('a permission can be attached to a user directly', function () {
    $role = createRole('Editor');
    $permission = createPermission('special override');
    $user = User::factory()->create();

    (new RolePermissionAction)->attach($role->id, $permission->id, 2, $user->id);

    expect($user->fresh()->hasPermissionTo($permission))->toBeTrue();
});

test('a direct permission can be revoked from a user', function () {
    $role = createRole('Editor');
    $permission = createPermission('special override');
    $user = User::factory()->create();

    (new RolePermissionAction)->attach($role->id, $permission->id, 2, $user->id);
    (new RolePermissionAction)->detach($role->id, $permission->id, 2, $user->id);

    expect($user->fresh()->hasPermissionTo($permission))->toBeFalse();
});

test('modifying super admin permissions is forbidden', function () {
    $role = createRole('Super Admin');
    $permission = createPermission('edit articles');

    expect(fn () => (new RolePermissionAction)->attach($role->id, $permission->id, 1))
        ->toThrow(HttpException::class, 'general.can_not_modify_super_admin_permissions');

    expect(fn () => (new RolePermissionAction)->detach($role->id, $permission->id, 1))
        ->toThrow(HttpException::class, 'general.can_not_modify_super_admin_permissions');
});

test('an unknown permission type is rejected', function (int $type) {
    $role = createRole('Editor');
    $permission = createPermission('edit articles');

    expect(fn () => (new RolePermissionAction)->attach($role->id, $permission->id, $type))
        ->toThrow(HttpException::class, 'general.invalid_permission_type');
})->with([0, 3, -1, 99]);

test('attaching to a missing role throws a model not found error', function () {
    $permission = createPermission('edit articles');

    expect(fn () => (new RolePermissionAction)->attach(999999, $permission->id, 1))
        ->toThrow(ModelNotFoundException::class);
});

test('a missing user id is rejected when targeting a user', function () {
    $role = createRole('Editor');
    $permission = createPermission('edit articles');

    expect(fn () => (new RolePermissionAction)->attach($role->id, $permission->id, 2, 999999))
        ->toThrow(ModelNotFoundException::class);
});

test('permission lookup uses the spatie permission model', function () {
    // The action resolves permissions through Spatie's model rather than the
    // application's Role model, so ids are looked up in the shared table.
    $permission = createPermission('edit articles');

    expect(Permission::findById($permission->id))->toBeInstanceOf(Permission::class);
});
