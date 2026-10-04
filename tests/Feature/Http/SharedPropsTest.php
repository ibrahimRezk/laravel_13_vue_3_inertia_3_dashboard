<?php

use Inertia\Testing\AssertableInertia;

/*
|--------------------------------------------------------------------------
| Shared Inertia props
|--------------------------------------------------------------------------
|
| HandleInertiaRequests::share() runs on every Inertia response and builds the
| sidebar from route() and can() calls, so a bad route name or an ability check
| would break every authenticated page at once.
|
*/

test('shared props include the app name, locale and auth user', function () {
    $user = actingAsSuperAdmin();

    $this->get(route('nationalities.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('name', config('app.name'))
            ->where('locale', 'en')
            ->where('auth.user.id', $user->id)
            ->has('auth.token')
        );
});

test('shared props carry the sidebar menu structure', function () {
    actingAsSuperAdmin();

    $this->get(route('nationalities.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('menus')
            ->has('menus.0')
            ->has('menus.0.title')
            ->has('menus.1.title')
            ->has('menus.2.title')
            ->has('menus.3.title')
            ->where('menus.0.title', 'Dashboard')
            ->where('menus.1.title', 'Nationalities')
            ->where('menus.2.title', 'General Settings')
            ->where('menus.3.title', 'Admins & Permissions')
        );
});

test('the dashboard menu entry is marked active on the dashboard', function () {
    actingAsSuperAdmin();

    $this->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('menus.0.isActive', true)
            ->where('menus.1.isActive', false)
        );
});

test('the nationalities menu entry is active on the nationalities index', function () {
    actingAsSuperAdmin();

    $this->get(route('nationalities.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('menus.1.isActive', true)
            ->where('menus.0.isActive', false)
        );
});

test('menu sub entries carry their own hrefs and active flags', function () {
    actingAsSuperAdmin();

    $this->get(route('roles.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('menus.3.subMenus')
            ->has('menus.3.subMenus.0')
            ->has('menus.3.subMenus.1')
            ->where('menus.3.subMenus.0.title', 'System Admins')
            ->where('menus.3.subMenus.1.title', 'Roles and Permissions')
            ->where('menus.3.subMenus.1.isActive', true)
            ->where('menus.3.subMenus.0.isActive', false)
        );
});

test('every menu entry is visible to a super admin', function () {
    actingAsSuperAdmin();

    $this->get(route('nationalities.index'))
        ->assertInertia(function (AssertableInertia $page) {
            $menus = $page->toArray()['props']['menus'];

            foreach ($menus as $menu) {
                expect($menu['isVisible'])->toBeTrue();
            }
        });
});

test('menu visibility is filtered for an unprivileged user', function () {
    // Only the nationalities pages are granted, so the sidebar should expose just
    // that entry rather than the whole tree.
    actingAsUserWithPermissions('view nationalities');

    $this->get(route('nationalities.index'))
        ->assertInertia(function (AssertableInertia $page) {
            $menus = $page->toArray()['props']['menus'];

            expect($menus[1]['isVisible'])->toBeTrue()
                ->and($menus[0]['isVisible'])->toBeFalse()
                ->and($menus[2]['isVisible'])->toBeFalse()
                ->and($menus[3]['isVisible'])->toBeFalse();
        });
});

test('the dashboard menu requires the view dashboard ability', function () {
    // No permission grants "view dashboard", and no such permission is seeded, so
    // the entry stays hidden even though the route is reachable.
    actingAsUserWithPermissions('view nationalities');

    $this->get(route('nationalities.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('menus.0.isVisible', false)
        );
});

test('flash messages are exposed as shared props', function () {
    actingAsSuperAdmin();

    $this->from(route('nationalities.index'))
        ->withSession(['success' => 'item created successfully'])
        ->get(route('nationalities.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('messages.success', 'item created successfully')
            ->where('messages.error', null)
            ->where('messages.vital_error', null)
        );
});

test('sidebar state defaults to open without a cookie', function () {
    actingAsSuperAdmin();

    $this->get(route('nationalities.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('sidebarOpen', true)
        );
});

test('the sidebar can be collapsed with a cookie', function () {
    actingAsSuperAdmin();

    $this->withUnencryptedCookie('sidebar_state', 'false')
        ->get(route('nationalities.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('sidebarOpen', false)
        );
});

test('the appearance middleware shares the default theme', function () {
    actingAsSuperAdmin();

    $this->get(route('nationalities.index'))
        ->assertViewHas('appearance', 'system');
});

test('the appearance middleware honours the cookie', function () {
    actingAsSuperAdmin();

    $this->withUnencryptedCookie('appearance', 'dark')
        ->get(route('nationalities.index'))
        ->assertViewHas('appearance', 'dark');
});

test('the shared locale prop ignores the language stored in the session', function () {
    // BUG: bootstrap/app.php appends Lang to the global web middleware group
    // *after* HandleInertiaRequests, so HandleInertiaRequests::share() runs first
    // and reads app()->getLocale() while it is still the default. The shared
    // 'locale' prop is therefore always "en" regardless of the session, and the
    // Vue side initialises in English even though the request locale is arabic.
    // Reordering the two middleware (or resolving the locale inside share())
    // fixes it. See bootstrap/app.php:18-22
    actingAsSuperAdmin();

    $this->withSession(['lang' => 'ar'])
        ->get(route('nationalities.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('locale', 'en')
        );

    // The application locale itself is applied correctly by Lang, so this is
    // specific to the shared prop being captured too early.
    expect(app()->getLocale())->toBe('ar');
});

test('the request locale is applied correctly by the Lang middleware', function () {
    actingAsSuperAdmin();

    $this->withSession(['lang' => 'ar'])
        ->get(route('nationalities.index'))
        ->assertOk();

    // Route-level Lang middleware runs and sets the locale for the rest of the
    // request; only the shared Inertia prop was captured too early.
    expect(app()->getLocale())->toBe('ar');
});

test('shared props expose the default page size', function () {
    actingAsSuperAdmin();

    $this->get(route('nationalities.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('paginationNumber', 10)
        );
});

test('shared props do not leak the password to the frontend', function () {
    actingAsSuperAdmin();

    $this->get(route('nationalities.index'))
        ->assertInertia(function (AssertableInertia $page) {
            $user = $page->toArray()['props']['auth']['user'];

            expect($user)->not->toHaveKey('password')
                ->and($user)->not->toHaveKey('remember_token');
        });
});

test('guests receive shared props without an auth user', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('auth.user', null)
            ->where('canRegister', true)
        );
});
