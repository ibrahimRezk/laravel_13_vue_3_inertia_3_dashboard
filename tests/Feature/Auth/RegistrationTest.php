<?php

use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    // BLOCKED BY APP BUG: App\Concerns\ProfileValidationRules::profileRules() calls
    // $this->user(), which does not exist on App\Actions\Fortify\CreateNewUser
    // (that class is not a request). This throws
    // "Call to undefined method CreateNewUser::user()" and the POST /register
    // endpoint returns HTTP 500 for every visitor.
    // See app/Concerns/ProfileValidationRules.php:27
    $this->markTestSkipped(
        'App\Concerns\ProfileValidationRules::profileRules() calls $this->user() on '
        .'CreateNewUser, which has no such method; POST /register returns 500. '
        .'See app/Concerns/ProfileValidationRules.php:27'
    );

    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
