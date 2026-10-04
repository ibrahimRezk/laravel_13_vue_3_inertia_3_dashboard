<?php

use App\Actions\Setting\UpdateSettingAction;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| UpdateSettingAction
|--------------------------------------------------------------------------
|
| The logo is written through AttachFilesTrait onto the "attachments" disk,
| so those tests fake it.
|
*/

function settingsPayload(array $overrides = []): array
{
    return array_merge([
        'name' => ['ar' => 'شركة', 'en' => 'Company'],
        'address' => ['ar' => 'عنوان', 'en' => 'Address'],
        'active' => true,
        'phone' => '01000000000',
        'email' => 'info@example.com',
        'weekendDays' => ['Friday', 'Saturday'],
    ], $overrides);
}

test('it creates the settings row when none exists', function () {
    actingAsSuperAdmin();

    expect(Setting::count())->toBe(0);

    $setting = (new UpdateSettingAction)->execute(
        Request::create('/systemSettings', 'POST'),
        settingsPayload()
    );

    expect(Setting::count())->toBe(1)
        ->and($setting->exists)->toBeTrue()
        ->and($setting->id)->toBe(1);
});

test('it stores both translations of the name and address', function () {
    actingAsSuperAdmin();

    $setting = (new UpdateSettingAction)->execute(
        Request::create('/systemSettings', 'POST'),
        settingsPayload()
    );

    expect($setting->fresh()->getTranslation('name', 'ar'))->toBe('شركة')
        ->and($setting->fresh()->getTranslation('name', 'en'))->toBe('Company')
        ->and($setting->fresh()->getTranslation('address', 'ar'))->toBe('عنوان')
        ->and($setting->fresh()->getTranslation('address', 'en'))->toBe('Address');
});

test('it stores the contact fields and weekend days', function () {
    actingAsSuperAdmin();

    $setting = (new UpdateSettingAction)->execute(
        Request::create('/systemSettings', 'POST'),
        settingsPayload()
    );

    expect($setting->fresh()->phone)->toBe('01000000000')
        ->and($setting->fresh()->email)->toBe('info@example.com')
        ->and($setting->fresh()->active)->toBeTrue()
        ->and($setting->fresh()->weekendDays)->toBe(['Friday', 'Saturday']);
});

test('updating twice reuses the same row', function () {
    actingAsSuperAdmin();

    (new UpdateSettingAction)->execute(Request::create('/systemSettings', 'POST'), settingsPayload());

    (new UpdateSettingAction)->execute(
        Request::create('/systemSettings', 'POST'),
        settingsPayload(['email' => 'new@example.com'])
    );

    expect(Setting::count())->toBe(1)
        ->and(Setting::first()->email)->toBe('new@example.com');
});

test('the first write records the acting user in added_by', function () {
    $actor = actingAsSuperAdmin();

    $setting = (new UpdateSettingAction)->execute(
        Request::create('/systemSettings', 'POST'),
        settingsPayload()
    );

    expect($setting->fresh()->added_by)->toBe($actor->id);
});

test('a later write records the acting user in updated_by', function () {
    $actor = actingAsSuperAdmin();

    (new UpdateSettingAction)->execute(Request::create('/systemSettings', 'POST'), settingsPayload());

    $setting = (new UpdateSettingAction)->execute(
        Request::create('/systemSettings', 'POST'),
        settingsPayload()
    );

    expect($setting->fresh()->updated_by)->toBe($actor->id);
});

test('a missing active flag stores false', function () {
    actingAsSuperAdmin();

    $payload = settingsPayload();
    unset($payload['active']);

    $setting = (new UpdateSettingAction)->execute(
        Request::create('/systemSettings', 'POST'),
        $payload
    );

    expect($setting->fresh()->active)->toBeFalse();
});

test('uploading a logo stores the generated file name', function () {
    Storage::fake('attachments');
    actingAsSuperAdmin();

    $request = Request::create('/systemSettings', 'POST');
    $request->files->set('logo', UploadedFile::fake()->image('logo.png'));

    $setting = (new UpdateSettingAction)->execute($request, settingsPayload());

    expect($setting->fresh()->logo)->not->toBeNull()
        ->and(Storage::disk('attachments')->allFiles('attachments/logo'))->not->toBeEmpty();
});

test('replacing the logo deletes the previous file', function () {
    Storage::fake('attachments');
    actingAsSuperAdmin();

    $first = Request::create('/systemSettings', 'POST');
    $first->files->set('logo', UploadedFile::fake()->image('first.png'));
    $original = (new UpdateSettingAction)->execute($first, settingsPayload())->fresh()->logo;

    expect(Storage::disk('attachments')->exists("attachments/logo/{$original}"))->toBeTrue();

    $second = Request::create('/systemSettings', 'POST');
    $second->files->set('logo', UploadedFile::fake()->image('second.png'));
    $replacement = (new UpdateSettingAction)->execute($second, settingsPayload())->fresh()->logo;

    expect(Storage::disk('attachments')->exists("attachments/logo/{$original}"))->toBeFalse()
        ->and(Storage::disk('attachments')->exists("attachments/logo/{$replacement}"))->toBeTrue();
});

test('saving without a logo keeps the existing one', function () {
    Storage::fake('attachments');
    actingAsSuperAdmin();

    $withLogo = Request::create('/systemSettings', 'POST');
    $withLogo->files->set('logo', UploadedFile::fake()->image('logo.png'));
    $original = (new UpdateSettingAction)->execute($withLogo, settingsPayload())->fresh()->logo;

    $withoutLogo = (new UpdateSettingAction)->execute(
        Request::create('/systemSettings', 'POST'),
        settingsPayload(['email' => 'changed@example.com'])
    )->fresh();

    expect($withoutLogo->logo)->toBe($original)
        ->and(Storage::disk('attachments')->exists("attachments/logo/{$original}"))->toBeTrue();
});

test('the weekend days attribute round-trips through json', function () {
    actingAsSuperAdmin();

    $setting = (new UpdateSettingAction)->execute(
        Request::create('/systemSettings', 'POST'),
        settingsPayload(['weekendDays' => ['Sunday']])
    );

    $fresh = $setting->fresh();

    expect($fresh->weekendDays)->toBe(['Sunday'])
        ->and(json_decode($fresh->getRawOriginal('weekendDays'), true))->toBe(['Sunday']);
});

test('the settings model exposes the relations used by the resource', function () {
    actingAsSuperAdmin();

    (new UpdateSettingAction)->execute(Request::create('/systemSettings', 'POST'), settingsPayload());

    $setting = Setting::first();

    // Setting declares $with = ['media', 'added_by_user', 'updated_by_user'].
    expect($setting->relationLoaded('media'))->toBeTrue()
        ->and($setting->added_by_user)->toBeInstanceOf(User::class);
});
