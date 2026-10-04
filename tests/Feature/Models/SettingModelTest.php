<?php

use App\Actions\Setting\UpdateSettingAction;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Setting model
|--------------------------------------------------------------------------
|
| App\Models\Setting declares weekendDays twice: once as an "array" cast in
| $casts, and again as an Attribute accessor/mutator. Laravel resolves the
| Attribute first (HasAttributes::transformModelValue checks hasAttributeMutator
| before hasCast), so the cast is dead code and the Attribute decides the
| behaviour. These tests pin which one actually wins.
|
*/

function createSetting(array $overrides = []): Setting
{
    // Setting::create() does not expand a Factory instance into a foreign key
    // the way a factory definition does, so the owner is created up front.
    $owner = User::factory()->create();

    return Setting::create(array_merge([
        'name' => ['ar' => 'شركة', 'en' => 'Company'],
        'address' => ['ar' => 'عنوان', 'en' => 'Address'],
        'phone' => '01000000000',
        'email' => 'info@example.com',
        'added_by' => $owner->id,
    ], $overrides));
}

test('weekend days is declared both as a cast and as an attribute', function () {
    $model = new Setting;

    // The duplication is the root cause: only one of the two can ever apply.
    expect($model->getCasts())->toHaveKey('weekendDays')
        ->and(method_exists($model, 'weekendDays'))->toBeTrue()
        ->and((new ReflectionMethod($model, 'weekendDays'))->getReturnType()?->getName())
        ->toBe(Attribute::class);
});

test('the attribute accessor wins over the array cast', function () {
    $setting = createSetting(['weekendDays' => ['Friday', 'Saturday']]);

    // Both definitions would decode the json column, so the observable result
    // is the same. What distinguishes them is the mutator, below.
    expect($setting->fresh()->weekendDays)->toBe(['Friday', 'Saturday']);
});

test('an array of days is stored as json', function () {
    $setting = createSetting(['weekendDays' => ['Friday']]);

    expect($setting->fresh()->getRawOriginal('weekendDays'))->toBe('["Friday"]');
});

test('reading weekend days returns a decoded array', function () {
    $setting = createSetting(['weekendDays' => ['Sunday', 'Monday']])->fresh();

    expect($setting->weekendDays)->toBeArray()
        ->toBe(['Sunday', 'Monday']);
});

test('reassigning an array of days overwrites rather than appends', function () {
    $setting = createSetting(['weekendDays' => ['Friday']]);

    $setting->weekendDays = ['Sunday'];
    $setting->save();

    expect($setting->fresh()->weekendDays)->toBe(['Sunday']);
});

test('assigning an already encoded json string double encodes the value', function () {
    // BUG: weekendDays has both an "array" cast and an Attribute mutator. The
    // Attribute's setter runs json_encode() on whatever it is given, so handing
    // it a value it already returned re-encodes the json string: the column ends
    // up holding the JSON scalar ["Friday"] instead of the array ["Friday"]. Any
    // code that reads the attribute back then gets a string rather than a list
    // of day names. See app/Models/Setting.php:51-57
    $setting = createSetting(['weekendDays' => ['Friday']]);

    $current = $setting->fresh()->weekendDays;
    expect($current)->toBe(['Friday']);

    $setting->weekendDays = json_encode($current);
    $setting->save();

    $raw = $setting->fresh()->getRawOriginal('weekendDays');

    // The Attribute re-encodes the already-encoded string, so the column now holds
    // a JSON string whose content is the json array, not a json array of names.
    expect($raw)->toBe(json_encode(json_encode(['Friday'])))
        ->and(json_decode($raw, true))->toBeString()
        ->and($setting->fresh()->weekendDays)->toBe('["Friday"]')
        ->and($setting->fresh()->weekendDays)->not->toBeArray();
});

test('the action stores weekend days without double encoding', function () {
    actingAsSuperAdmin();

    (new UpdateSettingAction)->execute(
        Request::create('/systemSettings', 'POST'),
        [
            'name' => ['ar' => 'شركة', 'en' => 'Company'],
            'address' => ['ar' => 'عنوان', 'en' => 'Address'],
            'active' => true,
            'phone' => '01000000000',
            'email' => 'info@example.com',
            'weekendDays' => ['Friday', 'Saturday'],
        ]
    );

    $setting = Setting::first();

    expect($setting->getRawOriginal('weekendDays'))->toBe('["Friday","Saturday"]')
        ->and($setting->weekendDays)->toBe(['Friday', 'Saturday']);
});

test('a second save through the action keeps the value stable', function () {
    actingAsSuperAdmin();

    $payload = [
        'name' => ['ar' => 'شركة', 'en' => 'Company'],
        'address' => ['ar' => 'عنوان', 'en' => 'Address'],
        'active' => true,
        'phone' => '01000000000',
        'email' => 'info@example.com',
        'weekendDays' => ['Friday', 'Saturday'],
    ];

    (new UpdateSettingAction)->execute(Request::create('/systemSettings', 'POST'), $payload);
    (new UpdateSettingAction)->execute(Request::create('/systemSettings', 'POST'), $payload);

    // Repeated saves must not accumulate encoding layers.
    expect(Setting::first()->getRawOriginal('weekendDays'))->toBe('["Friday","Saturday"]')
        ->and(Setting::first()->weekendDays)->toBe(['Friday', 'Saturday']);
});

test('empty weekend days are handled', function (mixed $value, mixed $expected) {
    $setting = createSetting(['weekendDays' => $value]);

    expect($setting->fresh()->weekendDays)->toBe($expected);
})->with([
    'empty array' => [[], []],
    'null' => [null, null],
]);
