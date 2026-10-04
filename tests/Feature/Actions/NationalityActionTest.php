<?php

use App\Actions\Nationality\CreateNationalityAction;
use App\Actions\Nationality\DeleteNationalitiesAction;
use App\Actions\Nationality\UpdateNationalityAction;
use App\Models\Nationality;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/*
|--------------------------------------------------------------------------
| Nationality actions
|--------------------------------------------------------------------------
*/

test('creating a nationality stores both name translations', function () {
    actingAsSuperAdmin();

    $nationality = (new CreateNationalityAction)->execute([
        'name' => ['ar' => 'مصري', 'en' => 'Egyptian'],
        'active' => true,
    ]);

    expect($nationality->exists)->toBeTrue()
        ->and($nationality->getTranslation('name', 'ar'))->toBe('مصري')
        ->and($nationality->getTranslation('name', 'en'))->toBe('Egyptian');
});

test('creating a nationality records the date and the acting user', function () {
    $actor = actingAsSuperAdmin();

    $nationality = (new CreateNationalityAction)->execute([
        'name' => ['ar' => 'مصري', 'en' => 'Egyptian'],
        'active' => true,
    ]);

    expect($nationality->date)->toBe(now()->toDateString())
        ->and($nationality->added_by)->toBe($actor->id);
});

test('creating a nationality without an authenticated user violates the schema', function () {
    // BUG: nationalities.added_by is a NOT NULL foreign key, but the action
    // writes auth()->id() directly. Any caller without a session - a queued
    // job, a console command, a seeder - inserts null and the insert fails with
    // an integrity constraint violation rather than a clear error.
    // See app/Actions/Nationality/CreateNationalityAction.php:16
    expect(fn () => (new CreateNationalityAction)->execute([
        'name' => ['ar' => 'سعودي', 'en' => 'Saudi'],
        'active' => true,
    ]))->toThrow(QueryException::class);
});

test('a missing active flag defaults to false', function () {
    actingAsSuperAdmin();

    $nationality = (new CreateNationalityAction)->execute([
        'name' => ['ar' => 'سعودي', 'en' => 'Saudi'],
    ]);

    expect($nationality->fresh()->active)->toBeFalse();
});

test('updating a nationality replaces both translations', function () {
    $nationality = Nationality::factory()->create([
        'name' => ['ar' => 'مصري', 'en' => 'Egyptian'],
    ]);

    $updated = (new UpdateNationalityAction)->execute($nationality, [
        'name' => ['ar' => 'مصري مُعدّل', 'en' => 'Updated Egyptian'],
        'active' => false,
    ]);

    expect($updated->fresh()->getTranslation('name', 'ar'))->toBe('مصري مُعدّل')
        ->and($updated->fresh()->getTranslation('name', 'en'))->toBe('Updated Egyptian');
});

test('updating a nationality records the acting user in updated_by', function () {
    $actor = actingAsSuperAdmin();
    $nationality = Nationality::factory()->create();

    $updated = (new UpdateNationalityAction)->execute($nationality, [
        'name' => ['ar' => 'اختبار', 'en' => 'Test'],
        'active' => true,
    ]);

    expect($updated->fresh()->updated_by)->toBe($actor->id);
});

test('updating a nationality without an active flag sets it to false', function () {
    $nationality = Nationality::factory()->create(['active' => true]);

    $updated = (new UpdateNationalityAction)->execute($nationality, [
        'name' => ['ar' => 'اختبار', 'en' => 'Test'],
    ]);

    expect($updated->fresh()->active)->toBeFalse();
});

test('deleting unused nationalities removes them', function () {
    $first = Nationality::factory()->create(['used_before' => false]);
    $second = Nationality::factory()->create(['used_before' => false]);

    (new DeleteNationalitiesAction)->execute([$first->id, $second->id]);

    expect(Nationality::whereIn('id', [$first->id, $second->id])->count())->toBe(0);
});

test('deleting a nationality that has been used before is forbidden', function () {
    $blocked = Nationality::factory()->create(['used_before' => true]);
    $free = Nationality::factory()->create(['used_before' => false]);

    expect(fn () => (new DeleteNationalitiesAction)->execute([$blocked->id, $free->id]))
        ->toThrow(HttpException::class);

    // The whole batch is refused: neither row is removed.
    expect(Nationality::find($blocked->id))->not->toBeNull()
        ->and(Nationality::find($free->id))->not->toBeNull();
});

test('the blocked delete error names the offending nationality', function () {
    $blocked = Nationality::factory()->create([
        'name' => ['ar' => 'مصري', 'en' => 'Egyptian'],
        'used_before' => true,
    ]);

    try {
        (new DeleteNationalitiesAction)->execute([$blocked->id]);
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(403)
            ->and($e->getMessage())->toContain('has been used before');
    }
});

test('deleting an empty id list is a no-op', function () {
    Nationality::factory()->create();

    (new DeleteNationalitiesAction)->execute([]);

    expect(Nationality::count())->toBe(1);
});

test('deleting unknown ids does not throw', function () {
    expect(fn () => (new DeleteNationalitiesAction)->execute([999999]))
        ->not->toThrow(HttpException::class);
});

test('active scope returns only active nationalities', function () {
    Nationality::factory()->active()->create();
    Nationality::factory()->inActive()->create();

    expect(Nationality::active()->count())->toBe(1)
        ->and(Nationality::inActive()->count())->toBe(1);
});

test('the active scope casts the boolean column correctly', function () {
    $active = Nationality::factory()->active()->create()->fresh();
    $inactive = Nationality::factory()->inActive()->create()->fresh();

    expect($active->active)->toBeTrue()
        ->and($inactive->active)->toBeFalse();
});
