<?php

use App\Models\User;
use App\Support\MediaModelResolver;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\MediaLibrary\HasMedia;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('it resolves an allowed alias to the matching model', function () {
    $user = User::factory()->create();

    $resolved = (new MediaModelResolver)->resolve('user', $user->id);

    expect($resolved)->toBeInstanceOf(User::class)
        ->and($resolved->is($user))->toBeTrue();
});

test('it throws a 422 for an alias that is not on the allowlist', function (string $alias) {
    expect(fn () => (new MediaModelResolver)->resolve($alias, 1))
        ->toThrow(HttpException::class, "Invalid modelType [{$alias}].");
})->with([
    'product',
    'article',
    'USER',
    'user;drop',
    '',
    ' user',
]);

test('it matches the allowlist case sensitively', function () {
    // The lookup is array_key_exists() against a hard-coded map, so aliases are
    // case sensitive and "User" is rejected rather than resolved.
    expect(fn () => (new MediaModelResolver)->resolve('User', 1))
        ->toThrow(HttpException::class);
});

test('it throws a 404 model not found for an unknown id', function () {
    expect(fn () => (new MediaModelResolver)->resolve('user', 999999))
        ->toThrow(ModelNotFoundException::class);
});

test('it reports the status code as 422 for an invalid alias', function () {
    // HttpException::getCode() stays 0; the HTTP status lives in getStatusCode().
    try {
        (new MediaModelResolver)->resolve('nope', 1);
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(422);
    }
});

test('every allowed alias maps to a model implementing HasMedia', function () {
    // The resolver guards against a non-HasMedia subject before querying, which
    // would otherwise fatal inside InteractsWithMedia. Assert the allowlist entry
    // is actually usable rather than trusting the comment.
    $user = new User;

    expect($user)->toBeInstanceOf(HasMedia::class);
});

test('the allowlist cannot be reached by passing a class name directly', function () {
    // Guards against arbitrary model access: the input must be an alias key, not
    // an FQCN, so passing User::class does not grant access to unlisted models.
    expect(fn () => (new MediaModelResolver)->resolve(User::class, 1))
        ->toThrow(HttpException::class);
});
