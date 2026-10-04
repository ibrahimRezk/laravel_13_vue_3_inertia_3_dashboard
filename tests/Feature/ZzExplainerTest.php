<?php

use Illuminate\Support\Facades\Validator;

/*
 * Demonstrates the two Laravel behaviours behind the reported 500 errors.
 * These are documentation-as-tests: each shows the framework contract that the
 * application code mis-assumes.
 */

// 1. nullable rule + key absent => key missing from validated()
test('nullable does not guarantee the key exists in validated', function (array $input, array $expectedKeys) {
    $v = Validator::make($input, [
        'collection' => ['nullable', 'string', 'max:100'],
    ]);

    expect($v->fails())->toBeFalse();

    $validated = $v->validated();

    foreach ($expectedKeys as $key) {
        expect($validated)->toHaveKey($key);
    }
})->with([
    'omitted entirely' => [[], []],
    'sent as empty string' => [['collection' => ''], ['collection']],
    'sent as null' => [['collection' => null], ['collection']],
]);

test('omitted key makes a direct read fatal', function () {
    $validated = Validator::make([], [
        'collection' => ['nullable', 'string', 'max:100'],
    ])->validated();

    // This is exactly app/Http/Controllers/ImageUploadController.php:35
    expect(fn () => $validated['collection'] ?: 'default')
        ->toThrow(ErrorException::class, 'Undefined array key "collection"');
});

test('empty string is present and the ?: fallback applies as written', function () {
    $validated = Validator::make(['collection' => ''], [
        'collection' => ['nullable', 'string', 'max:100'],
    ])->validated();

    expect($validated['collection'])->toBe('')
        ->and($validated['collection'] ?: 'default')->toBe('default');
});

// 2. wildcard rule + key absent => no rule fires, validation passes
test('a wildcard rule never fires when the whole key is missing', function () {
    $v = Validator::make(['active' => true], [
        'name.*' => ['required', 'string', 'max:255'],
        'active' => ['required', 'boolean'],
    ]);

    expect($v->fails())->toBeFalse()
        ->and($v->validated())->not->toHaveKey('name')
        ->and($v->validated())->not->toHaveKey('name.ar')
        ->and($v->validated())->not->toHaveKey('name.en');
});

test('a wildcard expands only to the translations actually present', function () {
    $v = Validator::make(['name' => ['en' => 'Test'], 'active' => true], [
        'name.*' => ['required', 'string', 'max:255'],
        'active' => ['required', 'boolean'],
    ]);

    // This is the crux of the nationality bug: "name.*" expands to name.en only,
    // because name.ar was never supplied. Laravel cannot require a key it has no
    // evidence exists, so validation passes and 'ar' is silently absent from the
    // validated payload that CreateNationalityAction then reads.
    expect($v->getRules())->toHaveKey('name.en')
        ->and($v->getRules())->not->toHaveKey('name.ar')
        ->and($v->fails())->toBeFalse()
        ->and($v->validated()['name'])->toBe(['en' => 'Test']);
});

test('a wildcard rule does fire when a present translation is null', function () {
    $v = Validator::make(['name' => ['ar' => null, 'en' => 'Test'], 'active' => true], [
        'name.*' => ['required', 'string', 'max:255'],
        'active' => ['required', 'boolean'],
    ]);

    // name.ar is present-but-null, so the expanded rule does apply and required
    // rejects it. The gap is specifically about keys that are absent entirely.
    expect($v->fails())->toBeTrue()
        ->and($v->errors()->keys())->toContain('name.ar');
});

test('an empty translations array passes the wildcard', function () {
    $v = Validator::make(['name' => [], 'active' => true], [
        'name.*' => ['required', 'string', 'max:255'],
        'active' => ['required', 'boolean'],
    ]);

    // No keys to expand to, so nothing is required and the array passes through.
    expect($v->fails())->toBeFalse()
        ->and($v->validated())->toHaveKey('name');
});

test('a wildcard rule passes when every present translation is valid', function () {
    $v = Validator::make([
        'name' => ['ar' => 'اسم', 'en' => 'Name'],
        'active' => true,
    ], [
        'name.*' => ['required', 'string', 'max:255'],
        'active' => ['required', 'boolean'],
    ]);

    expect($v->fails())->toBeFalse()
        ->and($v->validated())->toHaveKey('name');
});

test('reading a missing translation after a wildcard pass is fatal', function () {
    $validated = Validator::make(['active' => true], [
        'name.*' => ['required', 'string', 'max:255'],
        'active' => ['required', 'boolean'],
    ])->validated();

    // This is exactly app/Actions/Nationality/CreateNationalityAction.php:15
    expect(fn () => $validated['name']['ar'])
        ->toThrow(ErrorException::class, 'Undefined array key "name"');
});
