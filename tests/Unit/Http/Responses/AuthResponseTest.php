<?php

use App\Http\Responses\LoginResponse;
use App\Http\Responses\LogoutResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;

/*
|--------------------------------------------------------------------------
| Auth responses
|--------------------------------------------------------------------------
|
| Both contracts branch on wantsJson(). Note that neither response class is
| actually bound in FortifyServiceProvider — the singleton registrations are
| commented out at app/Providers/FortifyServiceProvider.php:51-66 — so these
| are unit tests of the classes themselves rather than of live auth flows.
|
*/

test('login returns a JSON payload for JSON requests', function () {
    $request = Request::create('/login', 'POST', server: ['HTTP_ACCEPT' => 'application/json']);

    $response = (new LoginResponse)->toResponse($request);

    expect($response->getStatusCode())->toBe(200)
        ->and(json_decode($response->getContent(), true))->toBe(['two_factor' => false]);
});

test('login redirects to the intended destination for browser requests', function () {
    $request = Request::create('/login', 'POST');

    $response = (new LoginResponse)->toResponse($request);

    expect($response->getStatusCode())->toBe(302);
});

test('login always reports two_factor as false in the JSON branch', function () {
    // The payload is hard-coded. It does not reflect whether the account actually
    // has two-factor authentication enabled, so a two-factor account is told the
    // challenge is not required. See app/Http/Responses/LoginResponse.php
    $request = Request::create('/login', 'POST', server: ['HTTP_ACCEPT' => 'application/json']);

    $response = (new LoginResponse)->toResponse($request);

    expect(json_decode($response->getContent(), true))->toHaveKey('two_factor', false);
});

test('logout returns an empty 204 for JSON requests', function () {
    $request = Request::create('/logout', 'POST', server: ['HTTP_ACCEPT' => 'application/json']);

    $response = (new LogoutResponse)->toResponse($request);

    // new JsonResponse('', 204) encodes the empty string, so the body is the
    // literal two-character sequence "" rather than an empty payload.
    expect($response->getStatusCode())->toBe(204)
        ->and($response->getContent())->toBe('""');
});

test('logout redirects browsers to the configured logout path', function () {
    $request = Request::create('/logout', 'POST');

    $response = (new LogoutResponse)->toResponse($request);

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toContain(Fortify::redirects('logout'));
});
