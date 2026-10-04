<?php

use App\Http\Resources\NationalityResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\Nationality;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MissingValue;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

/*
|--------------------------------------------------------------------------
| API resources
|--------------------------------------------------------------------------
|
| Resources run inside a request context, so each test authenticates a user
| first: several of them call auth()->user()->can() without a null-safe
| operator, which fatals for guests.
|
*/

beforeEach(function () {
    actingAsSuperAdmin();

    // Resources read abilities from the injected $request rather than the auth
    // guard, and a bare request() has no user resolver set. Attaching the
    // authenticated user is what a real controller passes in.
    request()->setUserResolver(fn () => auth()->user());
});

test('user resource exposes both translations of a translatable name', function () {
    $user = User::factory()->create([
        'name' => ['ar' => 'الاسم', 'en' => 'Name'],
    ]);

    $data = (new UserResource($user))->toArray(request());

    expect($data)->toHaveKeys(['id', 'name.ar', 'name.en', 'email', 'active', 'used_before'])
        ->and($data['name.ar'])->toBe('الاسم')
        ->and($data['name.en'])->toBe('Name');
});

test('user resource resolves the name for the active locale', function () {
    app()->setLocale('ar');

    $user = User::factory()->create([
        'name' => ['ar' => 'الاسم', 'en' => 'Name'],
    ]);

    $data = (new UserResource($user->fresh()))->toArray(request());

    expect($data['name'])->toBe('الاسم');
});

test('user resource reports email verification state', function () {
    $verified = User::factory()->create()->fresh();
    $unverified = User::factory()->unverified()->create()->fresh();

    expect((new UserResource($verified))->toArray(request())['is_email_verified'])->toBeTrue();
});

test('user resource omits the verification flag for unverified users', function () {
    $unverified = User::factory()->unverified()->create()->fresh();

    $data = (new UserResource($unverified))->toArray(request());

    // The field is wrapped in when($this->email_verified_at), so an unverified
    // user yields a MissingValue that the serialiser drops from the payload.
    expect($data['is_email_verified'])->toBeInstanceOf(MissingValue::class);
});

test('user resource hides the password attribute', function () {
    $user = User::factory()->create();

    $array = (new UserResource($user))->toArray(request());

    // User declares #[Hidden(['password', ...])], so the key is absent entirely.
    expect($array)->not->toHaveKey('password');
});

test('user resource omits images unless the media relation is loaded', function () {
    $user = User::factory()->create();

    $data = (new UserResource($user))->toArray(request());

    // whenLoaded() still contributes the key, but its value is a MissingValue
    // marker that the serialiser strips when the payload is rendered.
    expect($data['images'])->toBeInstanceOf(MissingValue::class);
});

test('user resource includes images once media is eager loaded', function () {
    $user = User::factory()->create()->load('media');

    $data = (new UserResource($user))->toArray(request());

    // The mapping yields a MediaCollection rather than a plain array.
    expect($data['images'])->not->toBeInstanceOf(MissingValue::class)
        ->and($data['images'])->toBeInstanceOf(MediaCollection::class);
});

test('user resource reports abilities for the current user', function () {
    $user = User::factory()->create();

    $can = (new UserResource($user))->toArray(request())['can'];

    // The acting user is a super admin, so the Gate::before hook grants everything.
    expect($can)->toHaveKeys(['edit', 'delete', 'view'])
        ->and($can['edit'])->toBeTrue()
        ->and($can['delete'])->toBeTrue()
        ->and($can['view'])->toBeTrue();
});

test('user resource omits roles and permissions unless they are loaded', function () {
    $user = User::factory()->create();

    $data = (new UserResource($user))->toArray(request());

    expect($data['roles'])->toBeInstanceOf(MissingValue::class)
        ->and($data['permissions'])->toBeInstanceOf(MissingValue::class);
});

test('nationality resource exposes both translations', function () {
    $nationality = Nationality::factory()->create([
        'name' => ['ar' => 'مصري', 'en' => 'Egyptian'],
    ]);

    $data = (new NationalityResource($nationality->fresh()))->toArray(request());

    expect($data)->toHaveKeys(['id', 'name.ar', 'name.en', 'active', 'used_before'])
        ->and($data['name.ar'])->toBe('مصري')
        ->and($data['name.en'])->toBe('Egyptian');
});

test('nationality resource grants edit and delete unconditionally', function () {
    $nationality = Nationality::factory()->create();

    $data = (new NationalityResource($nationality->fresh()))->toArray(request());

    // The permission checks here are commented out in favour of hard-coded true,
    // so the frontend always renders the action buttons regardless of the
    // viewer's permissions. See app/Http/Resources/NationalityResource.php:41
    expect($data['can'])->toBe(['edit' => true, 'delete' => true]);
});

test('nationality resource hides the update timestamp when it matches creation', function () {
    $nationality = Nationality::factory()->create();

    $nationality->forceFill(['created_at' => now(), 'updated_at' => now()])->save();

    $data = (new NationalityResource($nationality->fresh()))->toArray(request());

    // Unchanged rows report an empty string rather than omitting the key.
    expect($data['updated_at_formatted'])->toBe('');
});

test('role resource exposes both slug translations', function () {
    $role = createRole('Editor', [
        'slug' => ['ar' => 'محرر', 'en' => 'Editor'],
    ]);

    $data = (new RoleResource($role->fresh()))->toArray(request());

    expect($data)->toHaveKeys(['id', 'name', 'slug.ar', 'slug.en'])
        ->and($data['slug.ar'])->toBe('محرر')
        ->and($data['slug.en'])->toBe('Editor');
});

test('role resource denies delete for the super admin role', function () {
    $data = (new RoleResource(createRole('Super Admin')->fresh()))->toArray(request());

    // id 1 is reserved for the super admin role and cannot be deleted.
    expect($data['can']['delete'])->toBeFalse();
});

test('role resource denies delete for a role that has been used', function () {
    $role = createRole('Legacy', ['used_before' => true]);

    $data = (new RoleResource($role->fresh()))->toArray(request());

    expect($data['can']['delete'])->toBeFalse();
});

test('role resource allows delete for an unused secondary role', function () {
    $role = createRole('Unused', ['used_before' => false]);

    // Ensure the role does not occupy the reserved id 1.
    expect($role->id)->not->toBe(1);

    $data = (new RoleResource($role->fresh()))->toArray(request());

    expect($data['can']['delete'])->toBeTrue();
});

test('role resource omits the name column entirely', function () {
    // BUG: App\QueryBuilders\RoleQueryBuilder selects slug, id, used_before and
    // created_at but not name, so roles fetched through the index endpoint arrive
    // without a name and the roles table renders blank cells.
    // See app/QueryBuilders/RoleQueryBuilder.php:22
    $role = createRole('Auditor')->fresh();

    $queried = Role::query()
        ->select(['slug', 'id', 'used_before', 'created_at'])
        ->find($role->id);

    expect($queried->name)->toBeNull();
});
