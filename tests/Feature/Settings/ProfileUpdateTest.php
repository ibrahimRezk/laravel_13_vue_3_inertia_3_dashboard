<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => ['ar' => 'اسم المستخدم', 'en' => 'Test User'],
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('a string name is silently dropped by the profile update endpoint', function () {
    // BUG: App\Concerns\ProfileValidationRules declares the name rule as "name.*",
    // which only matches an array of translations. Posting a plain string passes
    // validation with no errors, but Validator::validated() omits "name" entirely,
    // so ProfileController::update() fills nothing and the profile is unchanged
    // while still redirecting with a success message.
    // See app/Concerns/ProfileValidationRules.php:20
    $user = User::factory()->create();
    $original = $user->name;

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'new-address@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    // The email is applied...
    expect($user->email)->toBe('new-address@example.com');

    // ...but the name is not, and the caller is never told why.
    expect($user->name)->toBe($original);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();

    // App\Models\User uses SoftDeletes, so the row survives with a deleted_at
    // timestamp rather than being removed outright.
    expect($user->fresh())->not->toBeNull()
        ->and($user->fresh()->trashed())->toBeTrue()
        ->and(User::query()->count())->toBe(0);
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh())->not->toBeNull();
});
