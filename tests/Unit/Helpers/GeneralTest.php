<?php

use Symfony\Component\HttpFoundation\InputBag;

/*
|--------------------------------------------------------------------------
| pagination() helper
|--------------------------------------------------------------------------
|
| app/Http/Helpers/General.php is autoloaded via composer.json "files", so
| pagination() is a global function rather than a namespaced helper class.
|
*/

test('pagination defaults to 10 when the parameter is absent', function () {
    request()->query = new InputBag;

    expect(pagination())->toBe(10);
});

test('pagination defaults to 10 when the parameter is null', function () {
    request()->query->set('paginationNumber', null);

    expect(pagination())->toBe(10);
});

test('pagination clamps a request for exactly 10 back to the default', function () {
    request()->query->set('paginationNumber', 10);

    // The helper treats 10 as "unset", so this is indistinguishable from no filter.
    expect(pagination())->toBe(10);
});

test('pagination returns the requested size', function () {
    request()->query->set('paginationNumber', 25);

    expect(pagination())->toBe(25);
});

test('pagination falls back to 10 for zero', function () {
    request()->query->set('paginationNumber', 0);

    // Guarded by "!= null": PHP's loose comparison treats 0 and null as equal,
    // so a 0 page size silently becomes 10 rather than paginating everything.
    expect(pagination())->toBe(10);
});

test('pagination returns a numeric string as a string', function () {
    request()->query->set('paginationNumber', '25');

    // Request values arrive as strings, so the return type is not stable:
    // an int literal yields int, but the same value over HTTP yields string.
    expect(pagination())->toBe('25');
});

test('pagination passes non-numeric values straight through', function () {
    request()->query->set('paginationNumber', 'abc');

    // BUG: no numeric guard, so "abc" reaches Builder::paginate() and the
    // query is built with a non-numeric LIMIT. See app/Http/Helpers/General.php
    expect(pagination())->toBe('abc');
});

test('pagination defaults to 10 for boolean-like values', function (mixed $value) {
    request()->query->set('paginationNumber', $value);

    expect(pagination())->toBe(10);
})->with([false, true, '']);
