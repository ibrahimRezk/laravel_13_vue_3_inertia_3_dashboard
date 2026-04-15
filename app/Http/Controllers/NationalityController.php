<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Inertia;
use App\Models\Nationality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
// use Diglactic\Breadcrumbs\Breadcrumbs;
use App\Http\Requests\NationalityRequest;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\NationalityResource;
use Illuminate\Routing\Controllers\Middleware;
class NationalityController extends Controller implements HasMiddleware
{

    // private string $routeResourceName = 'nationalities';

    // public function __construct()
    // {
    //     $this->middleware('can:view nationalities')->only('index');
    //     $this->middleware('can:create nationality')->only(['store']);
    //     $this->middleware('can:edit nationality')->only(['update']);
    //     $this->middleware('can:delete nationality')->only('destroy');
    // }

    public static function middleware(): array
    {
        return [
            // Apply 'can' middleware to specific methods
            new Middleware('can:view nationalities', only: ['index']),
            new Middleware('can:create nationality', only: ['store']),
            new Middleware('can:edit nationality', only: ['update']),
            new Middleware('can:delete nationality', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        // dd($request);
        $nationalities = Nationality::query()
            ->select([
                'id',
                'name',
                'used_before',

                'added_by',
                'updated_by',
                'active',
                'date',
                'created_at',
                'updated_at',

            ])
            ->with(['added_by_user:id,name', 'updated_by_user:id,name'])


            ->when($request->name, fn(Builder $builder, $name) => $builder->whereAny(['name->ar', 'name->en'], 'like', "%{$name}%"))

            /////////// very important her to add wherehas translation to call astrotomic translations /////////////////////////


            ->when(
                $request->active !== null,
                fn(Builder $builder) => $builder->when(
                    $request->active,
                    fn(Builder $builder) => $builder->active(),
                    fn(Builder $builder) => $builder->inActive()
                )
            )

            ->latest('id')  // to show oldest first
            // ->oldest('id')  // to show oldest first

            ->paginate(pagination())->onEachSide(1)->appends(request()->query());




        return Inertia::render('nationalities/index', [
            'title' => 'nationalities',
            'items' => NationalityResource::collection($nationalities),
            'headers' => [
                [
                    'label' => '#',
                    'name' => '#',
                ],
                [
                    'label' => 'name',
                    'name' => 'name',
                ],

                [
                    'label' => 'active',
                    'name' => 'active',
                ],


                [
                    'label' => 'added by',
                    'name' => 'added by',
                ],
                [
                    'label' => 'updated by',
                    'name' => 'updated by',
                ],
                [
                    'label' => 'created at',
                    'name' => 'created at',
                ],
                [
                    'label' => 'updated at',
                    'name' => 'updated at',
                ],
                [
                    'label' => 'actions',
                    'name' => 'actions',
                ],
            ],
            'filters' => (object) $request->all(),
            // 'routeResourceName' => $this->routeResourceName,
            'can' => [
                'create' => true,
                'delete' => true,
                // 'create' => $request->user()->can('create nationality'),
                // 'delete' => $request->user()->can('delete nationality'),
            ],

            'method' => 'index', // used in composable filters
            // 'breadcrumbs' => $breadcrumbs['breadcrumbs'],


        ]);
    }



    public function store(NationalityRequest $request)
    {
        DB::beginTransaction();
        try {
            // $data = $request->safe()->only(['active']);
            
            $data["name"]["ar"] = $request->name['ar'];
            $data["name"]["en"] = $request->name['en'];
            $data['active'] = $request->boolean('active');
            $data["date"] = date('Y-m-d');
            $data["added_by"] = auth()->user()->id;
            // $data["updated_by"] = auth()->user()->id;
            Nationality::create($data);
            DB::commit();

            return back()->with('success', 'item created successfully');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function update(NationalityRequest $request, Nationality $nationality)
    {
        DB::beginTransaction();
        try {

            $data = $request->safe()->only(['active']);

            $data["name"]["ar"] = $request->name['ar'];
            $data["name"]["en"] = $request->name['en'];
            $data['active'] = $request->boolean('active');
            $data["date"] = date('Y-m-d');
            // $data["added_by"] = auth()->user()->id;
            $data["updated_by"] = auth()->user()->id;



            $nationality->update($data);

            DB::commit();
            return back()->with('success', 'item updated successfully');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function destroy($ids)
    {
        $all_ids = explode(',', $ids);
        
        foreach ($all_ids as $id) {
            $nationality = Nationality::find($id);

            if ($nationality->used_before == true) {
                abort(403, 'general.this item used before and can not be deleted');
            }

            // only delete if not used before.
            $nationality->delete();
        }
        return redirect()->back()->with('success', 'item deleted successfully');
        ;

    }





}

