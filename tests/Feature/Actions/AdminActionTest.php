<?php

use App\Actions\Admin\CreateAdminAction;
use App\Actions\Admin\DeleteAdminsAction;
use App\Actions\Admin\UpdateAdminAction;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;

/*
|--------------------------------------------------------------------------
| Admin actions
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->role = createRole('Editor');
});

test('creating an admin creates the user, the admin profile and assigns the role', function () {
    $actor = actingAsSuperAdmin();

    $user = (new CreateAdminAction)->execute([
        'email' => 'editor@example.com',
        'name' => ['ar' => 'محرر', 'en' => 'Editor'],
        'password' => 'secret-password',
        'phone' => '01000000000',
        'active' => true,
    ], $this->role->id);

    expect($user->exists)->toBeTrue()
        ->and($user->email)->toBe('editor@example.com')
        ->and($user->getTranslation('name', 'en'))->toBe('Editor')
        ->and($user->getTranslation('name', 'ar'))->toBe('محرر')
        ->and($user->active)->toBeTrue()
        ->and($user->hasRole($this->role))->toBeTrue();
});

test('creating an admin hashes the supplied password', function () {
    actingAsSuperAdmin();

    $user = (new CreateAdminAction)->execute([
        'email' => 'editor@example.com',
        'name' => ['ar' => 'محرر', 'en' => 'Editor'],
        'password' => 'secret-password',
        'phone' => '01000000000',
        'active' => true,
    ], $this->role->id);

    expect($user->password)->not->toBe('secret-password')
        ->and(Hash::check('secret-password', $user->password))->toBeTrue();
});

test('creating an admin attaches an admin profile with the phone number', function () {
    $actor = actingAsSuperAdmin();

    $user = (new CreateAdminAction)->execute([
        'email' => 'editor@example.com',
        'name' => ['ar' => 'محرر', 'en' => 'Editor'],
        'password' => 'secret-password',
        'phone' => '01000000000',
        'active' => true,
    ], $this->role->id);

    $admin = Admin::findOrFail($user->profile_id);

    expect($admin)->not->toBeNull()
        ->and($admin->phone)->toBe('01000000000')
        ->and($admin->added_by)->toBe($actor->id)
        ->and($admin->user->is($user))->toBeTrue();
});

test('creating an admin flags the role as used', function () {
    actingAsSuperAdmin();

    (new CreateAdminAction)->execute([
        'email' => 'editor@example.com',
        'name' => ['ar' => 'محرر', 'en' => 'Editor'],
        'password' => 'secret-password',
        'phone' => '01000000000',
        'active' => true,
    ], $this->role->id);

    expect($this->role->fresh()->used_before)->toBeTrue();
});

test('creating an admin coerces the active flag to a boolean', function (mixed $input, bool $expected) {
    actingAsSuperAdmin();

    $user = (new CreateAdminAction)->execute([
        'email' => 'editor@example.com',
        'name' => ['ar' => 'محرر', 'en' => 'Editor'],
        'password' => 'secret-password',
        'phone' => '01000000000',
        'active' => $input,
    ], $this->role->id);

    expect($user->fresh()->active)->toBe($expected);
})->with([
    'int 1' => [1, true],
    'int 0' => [0, false],
    'string 1' => ['1', true],
    'string 0' => ['0', false],
    'true' => [true, true],
    'false' => [false, false],
]);

test('creating an admin with an unknown role id throws', function () {
    actingAsSuperAdmin();

    expect(fn () => (new CreateAdminAction)->execute([
        'email' => 'editor@example.com',
        'name' => ['ar' => 'محرر', 'en' => 'Editor'],
        'password' => 'secret-password',
        'phone' => '01000000000',
        'active' => true,
    ], 999999))->toThrow(ModelNotFoundException::class);
});

test('updating an admin changes the name, email and active flag', function () {
    $admin = userWithRoles([]);

    (new UpdateAdminAction)->execute($admin, [
        'email' => 'updated@example.com',
        'name' => ['ar' => 'اسم جديد', 'en' => 'New Name'],
        'phone' => '01111111111',
        'active' => false,
    ], $this->role->id);

    $admin = $admin->fresh();

    expect($admin->email)->toBe('updated@example.com')
        ->and($admin->getTranslation('name', 'en'))->toBe('New Name')
        ->and($admin->getTranslation('name', 'ar'))->toBe('اسم جديد')
        ->and($admin->active)->toBeFalse();
});

test('updating an admin replaces the role', function () {
    $other = createRole('Reviewer');
    $admin = userWithRoles(['Editor']);

    (new UpdateAdminAction)->execute($admin, [
        'email' => $admin->email,
        'name' => ['ar' => 'محرر', 'en' => 'Editor'],
        'phone' => '01000000000',
        'active' => true,
    ], $other->id);

    expect($admin->fresh()->hasRole($other))->toBeTrue()
        ->and($admin->fresh()->hasRole($this->role))->toBeFalse();
});

test('updating an admin leaves the password untouched when none is supplied', function () {
    $admin = userWithRoles(['Editor']);
    $originalHash = $admin->password;

    (new UpdateAdminAction)->execute($admin, [
        'email' => $admin->email,
        'name' => ['ar' => 'محرر', 'en' => 'Editor'],
        'phone' => '01000000000',
        'active' => true,
    ], $this->role->id);

    expect($admin->fresh()->password)->toBe($originalHash);
});

test('updating an admin changes the password when one is supplied', function () {
    $admin = userWithRoles(['Editor']);

    (new UpdateAdminAction)->execute($admin, [
        'email' => $admin->email,
        'name' => ['ar' => 'محرر', 'en' => 'Editor'],
        'phone' => '01000000000',
        'active' => true,
        'password' => 'brand-new-password',
    ], $this->role->id);

    expect(Hash::check('brand-new-password', $admin->fresh()->password))->toBeTrue();
});

test('updating an admin records the acting user in the profile', function () {
    actingAsSuperAdmin();

    $admin = (new CreateAdminAction)->execute([
        'email' => 'editor@example.com',
        'name' => ['ar' => 'محرر', 'en' => 'Editor'],
        'password' => 'secret-password',
        'phone' => '01000000000',
        'active' => true,
    ], $this->role->id);

    $actor = auth()->user();

    (new UpdateAdminAction)->execute($admin, [
        'email' => $admin->email,
        'name' => ['ar' => 'محرر', 'en' => 'Editor'],
        'phone' => '01111111111',
        'active' => true,
    ], $this->role->id);

    expect(Admin::find($admin->fresh()->profile_id)->updated_by)->toBe($actor->id);
});

test('updating a user without an admin profile silently skips the profile update', function () {
    // BUG: UpdateAdminAction guards the profile write with
    // "if ($admin = Admin::find($user->profile_id))", so a user record without a
    // morph profile updates its own columns and quietly drops the phone and
    // updated_by fields — no error, no warning.
    // See app/Actions/Admin/UpdateAdminAction.php:28
    $plain = userWithRoles([]);

    expect($plain->profile_id)->toBeNull();

    (new UpdateAdminAction)->execute($plain, [
        'email' => 'updated@example.com',
        'name' => ['ar' => 'اسم', 'en' => 'Name'],
        'phone' => '01000000000',
        'active' => true,
    ], $this->role->id);

    // The user columns are applied...
    expect($plain->fresh()->email)->toBe('updated@example.com')
        ->and(Admin::where('phone', '01000000000')->count())->toBe(0);
});

test('deleting admins removes both the users and their profiles', function () {
    actingAsSuperAdmin();

    $first = (new CreateAdminAction)->execute([
        'email' => 'first@example.com',
        'name' => ['ar' => 'أول', 'en' => 'First'],
        'password' => 'secret-password',
        'phone' => '01000000000',
        'active' => true,
    ], $this->role->id);

    $second = (new CreateAdminAction)->execute([
        'email' => 'second@example.com',
        'name' => ['ar' => 'ثاني', 'en' => 'Second'],
        'password' => 'secret-password',
        'phone' => '01000000000',
        'active' => true,
    ], $this->role->id);

    $adminIds = [$first->profile_id, $second->profile_id];

    (new DeleteAdminsAction)->execute([$first->id, $second->id]);

    // Admins are hard deleted, users are only soft deleted: the rows survive
    // with a deleted_at stamp because App\Models\User uses SoftDeletes.
    expect(Admin::whereIn('id', $adminIds)->count())->toBe(0)
        ->and(User::whereIn('id', [$first->id, $second->id])->count())->toBe(0)
        ->and(User::withTrashed()->find($first->id))->not->toBeNull()
        ->and(User::withTrashed()->find($first->id)->trashed())->toBeTrue();
});

test('deleting an admin who has been active before is forbidden', function () {
    actingAsSuperAdmin();

    $user = (new CreateAdminAction)->execute([
        'email' => 'busy@example.com',
        'name' => ['ar' => 'مشغول', 'en' => 'Busy'],
        'password' => 'secret-password',
        'phone' => '01000000000',
        'active' => true,
    ], $this->role->id);

    $user->forceFill(['used_before' => true])->save();

    expect(fn () => (new DeleteAdminsAction)->execute([$user->id]))
        ->toThrow(HttpException::class, 'You cannot delete an admin with previous activity on the system.');

    expect(User::withTrashed()->find($user->id))->not->toBeNull();
});

test('a blocked admin delete leaves the whole batch intact', function () {
    actingAsSuperAdmin();

    $blocked = (new CreateAdminAction)->execute([
        'email' => 'blocked@example.com',
        'name' => ['ar' => 'محظور', 'en' => 'Blocked'],
        'password' => 'secret-password',
        'phone' => '01000000000',
        'active' => true,
    ], $this->role->id);
    $blocked->forceFill(['used_before' => true])->save();

    $free = (new CreateAdminAction)->execute([
        'email' => 'free@example.com',
        'name' => ['ar' => 'حر', 'en' => 'Free'],
        'password' => 'secret-password',
        'phone' => '01000000000',
        'active' => true,
    ], $this->role->id);

    expect(fn () => (new DeleteAdminsAction)->execute([$blocked->id, $free->id]))
        ->toThrow(HttpException::class);

    expect(User::withTrashed()->whereIn('id', [$blocked->id, $free->id])->count())->toBe(2);
});

test('the admin model relates back to its user', function () {
    actingAsSuperAdmin();

    $user = (new CreateAdminAction)->execute([
        'email' => 'editor@example.com',
        'name' => ['ar' => 'محرر', 'en' => 'Editor'],
        'password' => 'secret-password',
        'phone' => '01000000000',
        'active' => true,
    ], $this->role->id);

    expect(Admin::find($user->profile_id)->user->is($user))->toBeTrue()
        ->and($user->fresh()->profile->is(Admin::find($user->profile_id)))->toBeTrue();
});
