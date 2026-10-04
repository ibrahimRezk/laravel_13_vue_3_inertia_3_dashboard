<?php

use App\Http\Resources\RoleResource;
use App\Models\Nationality;
use App\QueryBuilders\NationalityQueryBuilder;
use App\QueryBuilders\RoleQueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MissingValue;

/*
|--------------------------------------------------------------------------
| NationalityQueryBuilder
|--------------------------------------------------------------------------
|
| The builder reads filter values straight off the request, so each test
| builds a request carrying the query string a browser would send.
|
*/

/**
 * Build a request whose query string carries the given filters.
 */
function nationalityRequest(array $query = []): Request
{
    return Request::create('/nationalities?'.http_build_query($query), 'GET');
}

test('it returns every nationality when no filters are given', function () {
    Nationality::factory()->count(3)->create();

    $results = NationalityQueryBuilder::forRequest(nationalityRequest())->get();

    expect($results)->toHaveCount(3);
});

test('it filters by the english name', function () {
    $match = createNationality(['en' => 'Egyptian', 'ar' => 'مصري']);
    createNationality(['en' => 'Saudi', 'ar' => 'سعودي']);

    $results = NationalityQueryBuilder::forRequest(nationalityRequest(['name' => 'Egypt']))->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->is($match))->toBeTrue();
});

test('it filters by the arabic name', function () {
    createNationality(['en' => 'Egyptian', 'ar' => 'مصري']);
    $match = createNationality(['en' => 'Saudi', 'ar' => 'سعودي']);

    $results = NationalityQueryBuilder::forRequest(nationalityRequest(['name' => 'سعود']))->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->is($match))->toBeTrue();
});

test('it matches names with a partial value', function () {
    createNationality(['en' => 'Egyptian', 'ar' => 'مصري']);
    createNationality(['en' => 'Saudi', 'ar' => 'سعودي']);

    $results = NationalityQueryBuilder::forRequest(nationalityRequest(['name' => 'ian']))->get();

    expect($results)->toHaveCount(1);
});

test('an unmatched name filter returns nothing', function () {
    Nationality::factory()->count(2)->create();

    $results = NationalityQueryBuilder::forRequest(nationalityRequest(['name' => 'Nonexistent']))->get();

    expect($results)->toHaveCount(0);
});

test('an empty name filter is ignored', function () {
    Nationality::factory()->count(2)->create();

    // filled() is false for an empty string, so the filter never applies.
    $results = NationalityQueryBuilder::forRequest(nationalityRequest(['name' => '']))->get();

    expect($results)->toHaveCount(2);
});

test('it filters to active nationalities', function () {
    Nationality::factory()->active()->create();
    Nationality::factory()->inActive()->create();

    $results = NationalityQueryBuilder::forRequest(nationalityRequest(['active' => '1']))->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->active)->toBeTrue();
});

test('it filters to inactive nationalities', function () {
    Nationality::factory()->active()->create();
    Nationality::factory()->inActive()->create();

    $results = NationalityQueryBuilder::forRequest(nationalityRequest(['active' => '0']))->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->active)->toBeFalse();
});

test('an absent active filter does not narrow the results', function () {
    Nationality::factory()->active()->create();
    Nationality::factory()->inActive()->create();

    $results = NationalityQueryBuilder::forRequest(nationalityRequest())->get();

    expect($results)->toHaveCount(2);
});

test('it orders by id descending', function () {
    $first = createNationality(['en' => 'First']);
    $second = createNationality(['en' => 'Second']);
    $third = createNationality(['en' => 'Third']);

    $results = NationalityQueryBuilder::forRequest(nationalityRequest())->get();

    expect($results->pluck('id')->all())->toBe([$third->id, $second->id, $first->id]);
});

test('it eager loads the added by and updated by relations', function () {
    $nationality = createNationality();

    $results = NationalityQueryBuilder::forRequest(nationalityRequest())->get();

    expect($results->first()->relationLoaded('added_by_user'))->toBeTrue()
        ->and($results->first()->relationLoaded('updated_by_user'))->toBeTrue();
});

test('it returns a paginator that can be paged', function () {
    Nationality::factory()->count(15)->create();

    $paginator = NationalityQueryBuilder::forRequest(nationalityRequest())
        ->paginate(10)
        ->onEachSide(1)
        ->appends(nationalityRequest()->query());

    expect($paginator->total())->toBe(15)
        ->and($paginator->perPage())->toBe(10)
        ->and($paginator->lastPage())->toBe(2);
});

/*
|--------------------------------------------------------------------------
| RoleQueryBuilder
|--------------------------------------------------------------------------
*/

function roleRequest(array $query = []): Request
{
    return Request::create('/roles?'.http_build_query($query), 'GET');
}

test('roles are ordered by id ascending', function () {
    $first = createRole('Alpha');
    $second = createRole('Beta');

    $results = RoleQueryBuilder::forRequest(roleRequest())->get();

    expect($results->pluck('id')->all())->toBe([$first->id, $second->id]);
});

test('roles are filtered by the english slug', function () {
    $editor = createRole('Editor', ['slug' => ['ar' => 'محرر', 'en' => 'Editor']]);
    createRole('Reviewer', ['slug' => ['ar' => 'مراجع', 'en' => 'Reviewer']]);

    $results = RoleQueryBuilder::forRequest(roleRequest(['name' => 'Edit']))->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($editor->id);
});

test('roles are filtered by the arabic slug', function () {
    createRole('Editor', ['slug' => ['ar' => 'محرر', 'en' => 'Editor']]);
    $reviewer = createRole('Reviewer', ['slug' => ['ar' => 'مراجع', 'en' => 'Reviewer']]);

    $results = RoleQueryBuilder::forRequest(roleRequest(['name' => 'مراجع']))->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($reviewer->id);
});

test('a role filter with no match returns nothing', function () {
    createRole('Editor', ['slug' => ['ar' => 'محرر', 'en' => 'Editor']]);

    $results = RoleQueryBuilder::forRequest(roleRequest(['name' => 'Nonexistent']))->get();

    expect($results)->toHaveCount(0);
});

test('roles fetched through the builder have no name attribute', function () {
    // BUG: RoleQueryBuilder::forRequest() selects slug, id, used_before and
    // created_at but omits name. RoleResource then serialises a null name, so the
    // roles table renders blank name cells even though rows exist.
    // See app/QueryBuilders/RoleQueryBuilder.php:22
    $role = createRole('Editor', ['slug' => ['ar' => 'محرر', 'en' => 'Editor']]);

    $result = RoleQueryBuilder::forRequest(roleRequest())->first();

    expect($result->id)->toBe($role->id)
        ->and($result->name)->toBeNull()
        ->and($result->getTranslation('slug', 'en'))->toBe('Editor');
});

test('the roles index renders blank names through the resource', function () {
    // Same root cause, observed at the boundary the frontend consumes.
    actingAsSuperAdmin();
    request()->setUserResolver(fn () => auth()->user());

    createRole('Editor', ['slug' => ['ar' => 'محرر', 'en' => 'Editor']]);

    $item = RoleQueryBuilder::forRequest(roleRequest())->first();

    $payload = (new RoleResource($item))->toArray(request());

    // when(null, ...) collapses to a MissingValue marker rather than removing the
    // key outright; the serialiser drops it later, so the frontend sees no name.
    expect($payload['name'])->toBeInstanceOf(MissingValue::class)
        ->and($payload)->toHaveKey('slug.en');
});
