<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\NationalityController;
use App\Models\Admin;
use App\Models\Nationality;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

/*
|--------------------------------------------------------------------------
| HTTP endpoints
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->admin = actingAsSuperAdmin();
});

/*
|--------------------------------------------------------------------------
| Nationalities
|--------------------------------------------------------------------------
*/

test('the nationalities index renders for a super admin', function () {
    createNationality(['en' => 'Egyptian', 'ar' => 'مصري']);

    $this->get(route('nationalities.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('nationalities/index')
            ->where('title', 'nationalities')
            ->has('items')
            ->has('headers')
        );
});

test('the nationalities index exposes the table headers', function () {
    $this->get(route('nationalities.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('nationalities/index')
            ->has('headers', 8)
        );
});

test('the nationalities index paginates with the default page size', function () {
    Nationality::factory()->count(12)->create();

    $this->get(route('nationalities.index'))
        ->assertInertia(function (AssertableInertia $page) {
            $items = $page->toArray()['props']['items'];

            // JsonResource withoutWrapping() serialises the paginator into the
            // standard data / links / meta shape.
            expect($items)->toHaveKeys(['data', 'links', 'meta'])
                ->and($items['data'])->toHaveCount(10)
                ->and($items['meta']['total'])->toBe(12);
        });
});

test('the nationalities index honours a custom page size', function () {
    Nationality::factory()->count(12)->create();

    $this->get(route('nationalities.index', ['paginationNumber' => 5]))
        ->assertInertia(function (AssertableInertia $page) {
            $items = $page->toArray()['props']['items'];

            expect($items['data'])->toHaveCount(5)
                ->and($items['meta']['total'])->toBe(12);
        });
});

test('storing a nationality creates it and redirects back', function () {
    $response = $this->post(route('nationalities.store'), [
        'name' => ['ar' => 'مصري', 'en' => 'Egyptian'],
        'active' => true,
    ]);

    $response->assertSessionHasNoErrors()
        ->assertSessionHas('success');

    expect(Nationality::count())->toBe(1)
        ->and(Nationality::first()->getTranslation('name', 'en'))->toBe('Egyptian');
});

test('storing a nationality without a name returns a 500 rather than a validation error', function () {
    // BUG: NationalityRequest validates name with the wildcard rule "name.*", which
    // never fires when the whole key is absent, so validation passes. The action
    // then dereferences $validated['name']['ar'] and throws
    // "Undefined array key name", surfacing as HTTP 500 instead of a 422 with
    // field errors. See app/Http/Requests/NationalityRequest.php:27 and
    // app/Actions/Nationality/CreateNationalityAction.php:15
    $this->post(route('nationalities.store'), ['active' => true])
        ->assertStatus(500);

    expect(Nationality::count())->toBe(0);
});

test('a partially translated name also returns a 500', function () {
    // Same root cause as the missing-name case: "name.*" only expands to the
    // keys that are actually present, so supplying just "en" validates
    // successfully and the action then reads the absent 'ar' key.
    $this->post(route('nationalities.store'), [
        'name' => ['en' => 'Egyptian'],
        'active' => true,
    ])->assertStatus(500);

    expect(Nationality::count())->toBe(0);
});

test('storing a nationality rejects a duplicate translation', function () {
    createNationality(['en' => 'Egyptian', 'ar' => 'مصري']);

    $this->post(route('nationalities.store'), [
        'name' => ['ar' => 'مصري', 'en' => 'Another Egyptian'],
        'active' => true,
    ])->assertSessionHasErrors('name.ar');

    expect(Nationality::count())->toBe(1);
});

test('updating a nationality persists the changes', function () {
    $nationality = createNationality(['en' => 'Egyptian', 'ar' => 'مصري']);

    $this->patch(route('nationalities.update', $nationality), [
        'name' => ['ar' => 'معدّل', 'en' => 'Updated'],
        'active' => false,
    ])->assertSessionHasNoErrors();

    expect($nationality->fresh()->getTranslation('name', 'en'))->toBe('Updated')
        ->and($nationality->fresh()->active)->toBeFalse();
});

test('updating a nationality allows it to keep its own translation', function () {
    $nationality = createNationality(['en' => 'Egyptian', 'ar' => 'مصري']);

    $this->patch(route('nationalities.update', $nationality), [
        'name' => ['ar' => 'معدّل', 'en' => 'Egyptian'],
        'active' => true,
    ])->assertSessionHasNoErrors();
});

test('deleting a nationality removes it', function () {
    $nationality = createNationality();

    $this->delete(route('nationalities.destroy', $nationality->id))
        ->assertSessionHasNoErrors();

    expect(Nationality::find($nationality->id))->toBeNull();
});

test('deleting a nationality used before is refused', function () {
    // BUG: DeleteNationalitiesAction throws an HttpException(403) for a protected
    // row, but the controller does not catch it, so the request surfaces as an
    // unhandled 403 error page rather than a redirect with a flash message like
    // every other action. See app/Actions/Nationality/DeleteNationalitiesAction.php:17
    $nationality = createNationality([], ['used_before' => true]);

    $response = $this->delete(route('nationalities.destroy', $nationality->id));

    expect($response->getStatusCode())->toBe(403)
        ->and(Nationality::find($nationality->id))->not->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Roles
|--------------------------------------------------------------------------
*/

test('the roles index renders for a super admin', function () {
    createRole('Editor', ['slug' => ['ar' => 'محرر', 'en' => 'Editor']]);

    $this->get(route('roles.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('AdminsAndRoles/Roles/Index')
            ->where('title', 'roles')
            ->has('items')
        );
});

test('the roles index omits role names because of the select clause', function () {
    // BUG: RoleQueryBuilder selects slug, id, used_before and created_at but not
    // name, so each row in the roles table arrives without a name and the UI
    // renders a blank cell. Confirmed at
    // app/QueryBuilders/RoleQueryBuilder.php:22
    createRole('Editor', ['slug' => ['ar' => 'محرر', 'en' => 'Editor']]);

    $this->get(route('roles.index'))
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) {
            $items = $page->toArray()['props']['items'];

            // The paginated payload nests the rows under data.
            expect($items['data'])->not->toBeEmpty();

            foreach ($items['data'] as $item) {
                expect($item)->not->toHaveKey('name')
                    ->and($item)->toHaveKey('slug.en');
            }
        });
});

test('the roles create page renders', function () {
    $this->get(route('roles.create'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('AdminsAndRoles/Roles/Create')
            ->where('edit', false)
        );
});

test('storing a role creates it and redirects to the edit page', function () {
    $response = $this->post(route('roles.store'), [
        'name' => 'Editor',
        'slug' => ['ar' => 'محرر', 'en' => 'Editor'],
    ]);

    $role = Role::where('name', 'Editor')->firstOrFail();

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('roles.edit', $role));

    expect($role->getTranslation('slug', 'ar'))->toBe('محرر');
});

test('storing a role requires a unique name', function () {
    createRole('Editor');

    $this->post(route('roles.store'), [
        'name' => 'Editor',
        'slug' => ['ar' => 'محرر آخر', 'en' => 'Another Editor'],
    ])->assertSessionHasErrors('name');

    expect(Role::where('name', 'Editor')->count())->toBe(1);
});

test('the roles edit page renders with the page permissions', function () {
    $role = createRole('Editor', ['slug' => ['ar' => 'محرر', 'en' => 'Editor']]);

    $this->get(route('roles.edit', $role))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('AdminsAndRoles/Roles/Create')
            ->where('edit', true)
            ->has('item')
            ->has('pagesPermissions')
            ->has('specialPermissions')
        );
});

test('updating a role persists the changes', function () {
    $role = createRole('Editor', ['slug' => ['ar' => 'محرر', 'en' => 'Editor']]);

    $this->patch(route('roles.update', $role), [
        'name' => 'Editor',
        'slug' => ['ar' => 'محرر', 'en' => 'Senior Editor'],
    ])->assertSessionHasNoErrors()
        ->assertRedirect(route('roles.index'));

    expect($role->fresh()->getTranslation('slug', 'en'))->toBe('Senior Editor');
});

test('deleting an unused role removes it', function () {
    // The first role created occupies the reserved id 1 and is protected.
    createRole('Reserved First', ['slug' => ['ar' => 'محجوز', 'en' => 'Reserved']]);
    $role = createRole('Disposable', ['slug' => ['ar' => 'مؤقت', 'en' => 'Disposable']]);

    $this->delete(route('roles.destroy', $role))
        ->assertSessionHasNoErrors();

    expect(Role::find($role->id))->toBeNull();
});

test('attaching a permission to a role succeeds', function () {
    $role = createRole('Editor');
    $permission = createPermission('edit articles');

    $this->post(route('roles.attach-permission'), [
        'roleId' => $role->id,
        'permissionId' => $permission->id,
        'type' => 1,
    ])->assertOk()
        ->assertJson(['result' => 'success']);

    expect($role->fresh()->hasPermissionTo($permission))->toBeTrue();
});

test('detaching a permission from a role succeeds', function () {
    $role = createRole('Editor');
    $permission = createPermission('edit articles');
    $role->givePermissionTo($permission);

    $this->post(route('roles.detach-permission'), [
        'roleId' => $role->id,
        'permissionId' => $permission->id,
        'type' => 1,
    ])->assertOk()
        ->assertJson(['result' => 'success']);

    expect($role->fresh()->hasPermissionTo($permission))->toBeFalse();
});

test('attaching a permission to super admin reports an error instead of throwing', function () {
    $role = createRole('Super Admin');
    $permission = createPermission('edit articles');

    $this->post(route('roles.attach-permission'), [
        'roleId' => $role->id,
        'permissionId' => $permission->id,
        'type' => 1,
    ])->assertOk()
        ->assertJson(['result' => 'error']);

    expect($role->fresh()->hasPermissionTo($permission))->toBeFalse();
});

test('attaching a permission validates its payload', function () {
    $this->post(route('roles.attach-permission'), [])
        ->assertSessionHasErrors(['roleId', 'permissionId', 'type']);
});

test('attaching a permission requires a user id for the user target type', function () {
    $this->post(route('roles.attach-permission'), [
        'roleId' => createRole('Editor')->id,
        'permissionId' => createPermission('edit articles')->id,
        'type' => 2,
    ])->assertSessionHasErrors('userId');
});

/*
|--------------------------------------------------------------------------
| Admins
|--------------------------------------------------------------------------
*/

test('the admins index renders for a super admin', function () {
    $this->get(route('admins.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('AdminsAndRoles/Admins/Index')
            ->where('title', 'system admins')
            ->has('items')
            ->has('roles')
        );
});

test('the admins index excludes the authenticated user', function () {
    $other = User::factory()->create();

    $this->get(route('admins.index'))
        ->assertInertia(function (AssertableInertia $page) use ($other) {
            $items = $page->toArray()['props']['items'];
            $ids = array_column($items['data'], 'id');

            expect($ids)->toContain($other->id)
                ->and($ids)->not->toContain(auth()->id());
        });
});

test('storing an admin creates the user and profile', function () {
    $role = createRole('Editor');

    $this->post(route('admins.store'), [
        'email' => 'new-admin@example.com',
        'name' => ['ar' => 'مستخدم', 'en' => 'New Admin'],
        'password' => 'secret-password',
        'passwordConfirmation' => 'secret-password',
        'phone' => '01000000000',
        'roleId' => $role->id,
        'active' => true,
    ])->assertSessionHasNoErrors();

    $user = User::where('email', 'new-admin@example.com')->firstOrFail();

    expect($user->hasRole($role))->toBeTrue()
        ->and($user->profile_id)->not->toBeNull();
});

test('storing an admin requires the password fields to match', function () {
    $role = createRole('Editor');

    $this->post(route('admins.store'), [
        'email' => 'new-admin@example.com',
        'name' => ['ar' => 'مستخدم', 'en' => 'New Admin'],
        'password' => 'secret-password',
        'passwordConfirmation' => 'different-password',
        'phone' => '01000000000',
        'roleId' => $role->id,
        'active' => true,
    ])->assertSessionHasErrors('passwordConfirmation');

    expect(User::where('email', 'new-admin@example.com')->count())->toBe(0);
});

test('storing an admin requires a role that exists', function () {
    $this->post(route('admins.store'), [
        'email' => 'new-admin@example.com',
        'name' => ['ar' => 'مستخدم', 'en' => 'New Admin'],
        'password' => 'secret-password',
        'passwordConfirmation' => 'secret-password',
        'phone' => '01000000000',
        'roleId' => 999999,
        'active' => true,
    ])->assertSessionHasErrors('roleId');
});

test('storing an admin requires a unique email', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $role = createRole('Editor');

    $this->post(route('admins.store'), [
        'email' => 'taken@example.com',
        'name' => ['ar' => 'مستخدم', 'en' => 'New Admin'],
        'password' => 'secret-password',
        'passwordConfirmation' => 'secret-password',
        'phone' => '01000000000',
        'roleId' => $role->id,
        'active' => true,
    ])->assertSessionHasErrors('email');
});

test('the admin show page renders an existing admin', function () {
    $role = createRole('Editor');

    $user = User::factory()->create();
    $admin = Admin::create(['phone' => '01000000000', 'added_by' => auth()->id()]);
    $admin->user()->save($user);
    $user->assignRole($role);

    $this->get(route('admins.show', $user->id))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('AdminsAndRoles/Admins/Show')
            ->has('item')
            ->has('role')
            ->has('specialPermissions')
        );
});

test('the admins create route is registered but has no controller action', function () {
    // BUG: routes/web.php registers a full Route::resource for admins, but
    // AdminController implements no create() method, so GET /admins/create and
    // GET /admins/{user}/edit throw a 500 instead of rendering a page.
    // See app/Http/Controllers/AdminController.php
    expect(Route::has('admins.create'))->toBeTrue()
        ->and(method_exists(AdminController::class, 'create'))->toBeFalse()
        ->and(method_exists(AdminController::class, 'edit'))->toBeFalse();
});

test('requesting the admins create page returns a 500', function () {
    $this->withoutExceptionHandling();

    expect(fn () => $this->get(route('admins.create')))
        ->toThrow(Error::class, 'Call to undefined method App\Http\Controllers\AdminController::create()');
});

test('the nationalities create, show and edit routes have no controller actions', function () {
    // BUG: routes/web.php registers a full Route::resource for nationalities, but
    // NationalityController implements no create(), show() or edit() methods, so
    // all three routes throw a 500.
    // See app/Http/Controllers/NationalityController.php
    foreach (['create', 'show', 'edit'] as $method) {
        expect(method_exists(NationalityController::class, $method))->toBeFalse();
    }

    expect(Route::has('nationalities.create'))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| System settings
|--------------------------------------------------------------------------
*/

test('the system settings index renders when no settings exist yet', function () {
    $this->get(route('systemSettings.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('GeneralSettings/Settings/Index')
            ->where('title', 'System Settings')
            ->has('can')
        );
});

test('the system settings index renders the stored settings', function () {
    $this->post(route('systemSettings.store'), [
        'name' => ['ar' => 'شركة', 'en' => 'Company'],
        'address' => ['ar' => 'عنوان', 'en' => 'Address'],
        'active' => true,
        'phone' => '01000000000',
        'email' => 'info@example.com',
        'weekendDays' => ['Friday', 'Saturday'],
    ])->assertSessionHasNoErrors();

    $this->get(route('systemSettings.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('item')
            ->where('item.email', 'info@example.com')
        );
});

test('saving settings without weekend days returns a 500', function () {
    // BUG: "weekendDays" is declared nullable, so omitting it leaves the key out
    // of validated() entirely. UpdateSettingAction then reads
    // $validated['weekendDays'] unconditionally and throws
    // "Undefined array key weekendDays", turning a valid settings save into a 500.
    // See app/Actions/Setting/UpdateSettingAction.php:38
    $this->post(route('systemSettings.store'), [
        'name' => ['ar' => 'شركة', 'en' => 'Company'],
        'address' => ['ar' => 'عنوان', 'en' => 'Address'],
        'active' => true,
        'phone' => '01000000000',
        'email' => 'info@example.com',
    ])->assertStatus(500);

    expect(Setting::count())->toBe(0);
});

test('storing system settings requires the email to be valid', function () {
    $this->post(route('systemSettings.store'), [
        'name' => ['ar' => 'شركة', 'en' => 'Company'],
        'address' => ['ar' => 'عنوان', 'en' => 'Address'],
        'active' => true,
        'phone' => '01000000000',
        'email' => 'not-an-email',
    ])->assertSessionHasErrors('email');

    expect(Setting::count())->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Media
|--------------------------------------------------------------------------
*/

test('uploading an image without a collection name returns a 500', function () {
    // BUG: the "collection" rule is nullable, so when the field is omitted
    // Validator::validated() does not include the key at all. Line 35 then reads
    // $validated['collection'] unconditionally and throws
    // "Undefined array key collection", so every upload that relies on the
    // default collection name fails with HTTP 500.
    // See app/Http/Controllers/ImageUploadController.php:35
    Storage::fake(config('media-library.disk_name'));

    $target = User::factory()->create();

    $this->post(route('image.store'), [
        'file' => fakeUploadedImage(),
        'modelType' => 'user',
        'modelId' => $target->id,
    ])->assertStatus(500);

    expect($target->fresh()->getMedia())->toHaveCount(0);
});

test('uploading an image attaches it to the requested collection', function () {
    Storage::fake(config('media-library.disk_name'));

    $target = User::factory()->create();

    $this->post(route('image.store'), [
        'file' => fakeUploadedImage(),
        'modelType' => 'user',
        'modelId' => $target->id,
        'collection' => 'avatars',
    ])->assertCreated()
        ->assertJson(['success' => true, 'collection' => 'avatars']);

    expect($target->fresh()->getMedia('avatars'))->toHaveCount(1);
});

test('uploading an image rejects an unknown model type', function () {
    Storage::fake(config('media-library.disk_name'));

    $this->post(route('image.store'), [
        'file' => fakeUploadedImage(),
        'modelType' => 'product',
        'modelId' => 1,
    ])->assertStatus(422);
});

test('uploading an image requires a file', function () {
    $this->post(route('image.store'), [
        'modelType' => 'user',
        'modelId' => 1,
    ])->assertSessionHasErrors('file');
});

test('deleting an image that does not exist is not an error', function () {
    $this->delete(route('image.destroy', 999999))
        ->assertOk()
        ->assertJson(['success' => true]);
});

/*
|--------------------------------------------------------------------------
| Locale switching
|--------------------------------------------------------------------------
*/

test('switching the language stores it in the session', function () {
    $this->from(route('nationalities.index'))
        ->get(route('lang', ['locale' => 'ar']))
        ->assertRedirect(route('nationalities.index'))
        ->assertSessionHas('lang', 'ar');

    expect(app()->getLocale())->toBe('ar');
});

test('switching the language accepts an arbitrary locale', function () {
    // BUG: the change_lang route applies any string to the session without
    // validating it, so a typo silently becomes the active locale for the user.
    // See routes/web.php:87
    $this->from(route('nationalities.index'))
        ->get(route('lang', ['locale' => 'klingon']))
        ->assertRedirect(route('nationalities.index'))
        ->assertSessionHas('lang', 'klingon');

    expect(app()->getLocale())->toBe('klingon');
});
