<?php

use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Media\DeleteMediaAction;
use App\Actions\Media\StoreMediaAction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| Password reset action
|--------------------------------------------------------------------------
*/

test('resetting a password stores the new value', function () {
    $user = User::factory()->create();
    $original = $user->password;

    (new ResetUserPassword)->reset($user, [
        'password' => 'a-brand-new-password',
        'password_confirmation' => 'a-brand-new-password',
    ]);

    $user->refresh();

    expect($user->password)->not->toBe($original)
        ->and(Hash::check('a-brand-new-password', $user->password))->toBeTrue();
});

test('resetting a password leaves other attributes untouched', function () {
    $user = User::factory()->create(['name' => ['ar' => 'الاسم', 'en' => 'Original']]);

    (new ResetUserPassword)->reset($user, [
        'password' => 'a-brand-new-password',
        'password_confirmation' => 'a-brand-new-password',
    ]);

    expect($user->fresh()->name)->toBe('Original')
        ->and($user->fresh()->email)->toBe($user->email);
});

test('resetting a password rejects a short password', function () {
    $user = User::factory()->create();
    $original = $user->password;

    expect(fn () => (new ResetUserPassword)->reset($user, [
        'password' => 'abc',
        'password_confirmation' => 'abc',
    ]))->toThrow(ValidationException::class);

    expect($user->fresh()->password)->toBe($original);
});

test('resetting a password rejects a mismatched confirmation', function () {
    $user = User::factory()->create();

    expect(fn () => (new ResetUserPassword)->reset($user, [
        'password' => 'a-brand-new-password',
        'password_confirmation' => 'something-else',
    ]))->toThrow(ValidationException::class);
});

/*
|--------------------------------------------------------------------------
| DeleteMediaAction
|--------------------------------------------------------------------------
*/

/**
 * Real JPEG bytes, generated once per process.
 *
 * UploadedFile::fake()->image() builds its file from tmpfile(), which Windows
 * deletes as soon as the last handle closes, so the bytes are read in the same
 * expression that creates the handle.
 */
function deleteTestJpegBytes(): string
{
    static $bytes = null;

    if ($bytes === null) {
        $file = UploadedFile::fake()->image('seed.jpg');
        $bytes = (string) file_get_contents($file->getRealPath());
        unset($file);
    }

    return $bytes;
}

/**
 * Replace the globally bound request with one carrying a fake image.
 */
function bindDeleteTestUpload(string $filename = 'photo.jpg'): void
{
    $path = tempnam(sys_get_temp_dir(), 'pest-del-');
    file_put_contents($path, deleteTestJpegBytes());

    $request = Request::create('/upload-image', 'POST');
    $request->files->set('file', new UploadedFile($path, $filename, 'image/jpeg', null, true));

    app()->instance('request', $request);
}

test('deleting media removes the record', function () {
    Storage::fake(config('media-library.disk_name'));

    $user = User::factory()->create();
    bindDeleteTestUpload();

    $media = (new StoreMediaAction)->execute(request(), $user, 'default');

    expect($user->fresh()->getMedia('default'))->toHaveCount(1);

    (new DeleteMediaAction)->execute($media);

    expect($user->fresh()->getMedia('default'))->toHaveCount(0);
});

test('deleting media removes the file from disk', function () {
    Storage::fake(config('media-library.disk_name'));

    $user = User::factory()->create();
    bindDeleteTestUpload();

    $media = (new StoreMediaAction)->execute(request(), $user, 'default');

    Storage::disk(config('media-library.disk_name'))
        ->assertExists("{$media->id}/{$media->file_name}");

    (new DeleteMediaAction)->execute($media);

    Storage::disk(config('media-library.disk_name'))
        ->assertMissing("{$media->id}/{$media->file_name}");
});

test('deleting media only removes the targeted item', function () {
    Storage::fake(config('media-library.disk_name'));

    $user = User::factory()->create();

    bindDeleteTestUpload('first.jpg');
    $first = (new StoreMediaAction)->execute(request(), $user, 'default');

    bindDeleteTestUpload('second.jpg');
    (new StoreMediaAction)->execute(request(), $user, 'default');

    (new DeleteMediaAction)->execute($first);

    $remaining = $user->fresh()->getMedia('default');

    expect($remaining)->toHaveCount(1)
        ->and($remaining->first()->file_name)->toBe('second.jpg');
});

test('deleting media is safe to call on an already deleted item', function () {
    Storage::fake(config('media-library.disk_name'));

    $user = User::factory()->create();
    bindDeleteTestUpload();

    $media = (new StoreMediaAction)->execute(request(), $user, 'default');

    (new DeleteMediaAction)->execute($media);

    // The model instance remains in memory, so a second delete runs again
    // against a row that is already gone.
    expect(fn () => (new DeleteMediaAction)->execute($media))
        ->not->toThrow(Throwable::class);
});
