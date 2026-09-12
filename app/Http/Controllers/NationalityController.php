<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Nationality;
use Illuminate\Http\Request;
use App\Http\Requests\NationalityRequest;
use App\Http\Resources\NationalityResource;
use App\QueryBuilders\NationalityQueryBuilder;
use App\Actions\Nationality\CreateNationalityAction;
use App\Actions\Nationality\UpdateNationalityAction;
use App\Actions\Nationality\DeleteNationalitiesAction;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\RedirectResponse;

class NationalityController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view nationalities', only: ['index']),
            new Middleware('can:create nationality', only: ['store']),
            new Middleware('can:edit nationality', only: ['update']),
            new Middleware('can:delete nationality', only: ['destroy']),
        ];
    }

    public function index(Request $request): Response
    {
        $nationalities = NationalityQueryBuilder::forRequest($request)
            ->paginate(pagination())
            ->onEachSide(1)
            ->appends($request->query());

        return Inertia::render('nationalities/index', [
            'title'   => 'nationalities',
            'items'   => NationalityResource::collection($nationalities),
            'headers' => $this->tableHeaders(),
            'filters' => (object) $request->all(),
            'can'     => [
                'create' => true,
                'delete' => true,
            ],
            'method'  => 'index',
        ]);
    }

    public function store(NationalityRequest $request, CreateNationalityAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return back()->with('success', 'item created successfully');
    }

    public function update(NationalityRequest $request, Nationality $nationality, UpdateNationalityAction $action): RedirectResponse
    {
        $action->execute($nationality, $request->validated());

        return back()->with('success', 'item updated successfully');
    }

    public function destroy( $ids, DeleteNationalitiesAction $action): RedirectResponse
    {
        $ids = array_map('intval', explode(',', $ids));

        $action->execute($ids);

        return back()->with('success', 'item deleted successfully');
    }

    private function tableHeaders(): array
    {
        return collect(['#', 'name', 'active', 'added by', 'updated by', 'created at', 'updated at', 'actions'])
            ->map(fn ($label) => ['label' => $label, 'name' => $label])
            ->all();
    }
}