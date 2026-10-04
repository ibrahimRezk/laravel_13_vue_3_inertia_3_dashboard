<?php

use App\Actions\Media\StoreMediaAction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\RequestDoesNotHaveFile;
use Symfony\Component\HttpKernel\Exception\HttpException;

/*
|--------------------------------------------------------------------------
| StoreMediaAction
|--------------------------------------------------------------------------
|
| Note: spatie/laravel-medialibrary's addMediaFromRequest() reads the global
| request() helper rather than an injected instance (see
| vendor/.../MediaCollections/FileAdderFactory.php:45), so these tests swap the
| bound request to simulate an upload.
|
*/

/**
 * Replace the globally bound request with one carrying a single fake file.
 *
 * The payload is real JPEG data written into a temp file so that medialibrary's
 * mime sniffing accepts it, while the client filename stays under our control
 * for the sanitisation assertions.
 */
function bindUploadRequest(string $filename = 'photo.jpg'): void
{
    $request = Request::create('/upload-image', 'POST');
    $request->files->set('file', fakeJpegNamed($filename));

    app()->instance('request', $request);
}

/**
 * Real JPEG bytes, generated once per process.
 *
 * UploadedFile::fake()->image() builds its file from tmpfile(), which Windows
 * deletes the moment the last handle closes. The bytes are therefore read in
 * the same expression that creates the handle, and cached in memory.
 */
function jpegBytes(): string
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
 * Build a fake upload whose client filename is $filename but whose contents
 * are real image bytes matching the extension, so medialibrary's mime sniffing
 * accepts it while the client filename stays under our control.
 */
function fakeJpegNamed(string $filename): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'pest-img-');

    file_put_contents($path, jpegBytes());

    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $mime = match ($extension) {
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        default => 'image/jpeg',
    };

    return new UploadedFile($path, $filename, $mime, null, true);
}

beforeEach(function () {
    Storage::fake(config('media-library.disk_name'));
});

test('it stores an uploaded image on the model', function () {
    $user = User::factory()->create();
    bindUploadRequest('avatar.jpg');

    $media = (new StoreMediaAction)->execute(request(), $user, 'default');

    expect($media)->not->toBeNull()
        ->and($user->fresh()->getMedia('default'))->toHaveCount(1)
        ->and($media->collection_name)->toBe('default');
});

test('it sanitises the stored file name', function () {
    $user = User::factory()->create();
    bindUploadRequest('my holiday photo.jpg');

    $media = (new StoreMediaAction)->execute(request(), $user, 'default');

    expect($media->file_name)->toBe('my_holiday_photo.jpg');
});

test('it strips directory traversal segments from the file name', function () {
    $user = User::factory()->create();
    bindUploadRequest('../../etc/passwd.jpg');

    $media = (new StoreMediaAction)->execute(request(), $user, 'default');

    // Two layers of defence: Symfony's UploadedFile::getClientOriginalName()
    // already applies basename(), dropping "../../etc/" before the action runs.
    // sanitizeFilename() then removes any remaining separators and leading dots.
    expect($media->file_name)->toBe('passwd.jpg')
        ->and($media->file_name)->not->toContain('..')
        ->and($media->file_name)->not->toContain('/');
});

test('it stores into the requested collection', function () {
    $user = User::factory()->create();
    bindUploadRequest();

    (new StoreMediaAction)->execute(request(), $user, 'avatars');

    expect($user->fresh()->getMedia('avatars'))->toHaveCount(1)
        ->and($user->fresh()->getMedia('default'))->toHaveCount(0);
});

test('it enforces the per collection file cap', function () {
    $user = User::factory()->create();
    $action = new StoreMediaAction;

    // Fill the collection to its advertised maximum.
    foreach (range(1, 10) as $index) {
        bindUploadRequest("image{$index}.jpg");
        $action->execute(request(), $user, 'default');
    }

    expect($user->fresh()->getMedia('default'))->toHaveCount(10);

    // BUG (off-by-one): the guard reads "if ($current >= 10) throw" and runs
    // before the insert, so with exactly 10 files present the next upload still
    // succeeds. A collection therefore ends up holding 11 files, and only the
    // twelfth is rejected — one more than the message advertises.
    // See app/Actions/Media/StoreMediaAction.php:22
    bindUploadRequest('eleventh.jpg');
    $action->execute(request(), $user, 'default');

    expect($user->fresh()->getMedia('default'))->toHaveCount(11);
});

test('the twelfth upload is rejected with a 422', function () {
    $user = User::factory()->create();
    $action = new StoreMediaAction;

    foreach (range(1, 11) as $index) {
        bindUploadRequest("image{$index}.jpg");
        $action->execute(request(), $user, 'default');
    }

    bindUploadRequest('twelfth.jpg');

    expect(fn () => $action->execute(request(), $user, 'default'))
        ->toThrow(HttpException::class, "Maximum 10 images allowed in collection 'default'.");

    expect($user->fresh()->getMedia('default'))->toHaveCount(11);
});

test('the cap is applied per collection rather than per model', function () {
    $user = User::factory()->create();
    $action = new StoreMediaAction;

    foreach (range(1, 10) as $index) {
        bindUploadRequest("image{$index}.jpg");
        $action->execute(request(), $user, 'default');
    }

    // A different collection on the same model still has room.
    bindUploadRequest('other.jpg');
    $action->execute(request(), $user, 'other');

    expect($user->fresh()->getMedia('other'))->toHaveCount(1);
});

test('it throws when no file is present on the request', function () {
    $user = User::factory()->create();

    app()->instance('request', Request::create('/upload-image', 'POST'));

    expect(fn () => (new StoreMediaAction)->execute(request(), $user, 'default'))
        ->toThrow(RequestDoesNotHaveFile::class);
});

test('sanitizeFilename falls back to upload when stripping leaves nothing', function (string $input, string $expected) {
    $method = new ReflectionMethod(StoreMediaAction::class, 'sanitizeFilename');

    expect($method->invoke(new StoreMediaAction, $input))->toBe($expected);
})->with([
    ['...', 'upload'],
    ['   ', '_'],
    ['', 'upload'],
    ['a/b\\c.png', 'abc.png'],
    ["tab\there.png", 'tab_here.png'],
    ['photo.PNG', 'photo.PNG'],
    ['my photo.png', 'my_photo.png'],
]);
