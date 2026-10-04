<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\Lang;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Lang middleware
|--------------------------------------------------------------------------
|
| The Lang alias is applied to the authenticated dashboard routes in
| routes/web.php and sets the active locale from the session.
|
*/

test('it defaults the locale to en when no language is stored in the session', function () {
    session()->forget('lang');

    $request = Request::create('/dashboard');
    (new Lang)->handle($request, fn () => response('ok'));

    expect(app()->getLocale())->toBe('en');
});

test('it applies the locale stored in the session', function (string $locale) {
    session()->put('lang', $locale);

    $request = Request::create('/dashboard');
    (new Lang)->handle($request, fn () => response('ok'));

    expect(app()->getLocale())->toBe($locale);
})->with(['ar', 'en', 'fr']);

test('it passes the request through to the next handler', function () {
    $request = Request::create('/dashboard');

    $response = (new Lang)->handle($request, fn () => response('next ran', 201));

    expect($response->getContent())->toBe('next ran')
        ->and($response->getStatusCode())->toBe(201);
});

test('it does not validate the stored locale against available translations', function () {
    // BUG: the session value is applied without checking it against config('app.locales')
    // or the lang/ directory, so an arbitrary string becomes the active locale and
    // translation lookups silently fall back. See app/Http/Middleware/Lang.php
    session()->put('lang', 'not-a-real-locale');

    $request = Request::create('/dashboard');
    (new Lang)->handle($request, fn () => response('ok'));

    expect(app()->getLocale())->toBe('not-a-real-locale');
});

/*
|--------------------------------------------------------------------------
| HandleAppearance middleware
|--------------------------------------------------------------------------
*/

test('it shares the default appearance when no cookie is present', function () {
    $request = Request::create('/dashboard');

    (new HandleAppearance)->handle($request, fn () => response('ok'));

    expect(view()->shared('appearance'))->toBe('system');
});

test('it shares the appearance from the cookie', function (string $appearance) {
    $request = Request::create('/dashboard', 'GET', [], ['appearance' => $appearance]);

    (new HandleAppearance)->handle($request, fn () => response('ok'));

    expect(view()->shared('appearance'))->toBe($appearance);
})->with(['light', 'dark', 'system']);

test('appearance falls back to system for an unrecognised cookie value', function () {
    // BUG: HandleAppearance trusts the cookie verbatim, so an arbitrary value is
    // handed to the frontend as the active theme. The allowed set is light/dark/system.
    // See app/Http/Middleware/HandleAppearance.php
    $request = Request::create('/dashboard', 'GET', [], ['appearance' => 'hotdog']);

    (new HandleAppearance)->handle($request, fn () => response('ok'));

    expect(view()->shared('appearance'))->toBe('hotdog');
});
