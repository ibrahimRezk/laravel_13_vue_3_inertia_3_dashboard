<?php

namespace App\Http\Controllers;


use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Inertia;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Http\Traits\AttachFilesTrait;
use App\Http\Resources\SettingResource;
use Illuminate\Routing\Controllers\Middleware;

class SystemSettingController extends Controller implements HasMiddleware
{
    use AttachFilesTrait;


    private string $routeResourceName = 'settings';

    // public function __construct()
    // {
    //     $this->middleware('can:view settings')->only('index');
    //     $this->middleware('can:edit setting')->only(['store']);
    // }

            public static function middleware(): array
    {
        return [
            // Apply 'can' middleware to specific methods
            new Middleware('can:view system settings', only: ['index']),
            new Middleware('can:edit system settings', only: [ 'store']),

        ];
    }


    public function index(Request $request)
    {
        $settings = Setting::first();

        // $breadcrumbs = Breadcrumbs::render('settings');

        // ddd($settings);
        return Inertia::render('GeneralSettings/Settings/Index', [
            'title' => 'System Settings',
            'item' => $settings != null ? new SettingResource($settings) : null,
            'logoPath' => asset('attachments/logo/' . $settings?->logo) ?? '',
            // 'routeResourceName' => $this->routeResourceName,
            'can' => [
                'edit' => $request->user()->can('edit settings'),
            ],
            // 'breadcrumbs' => $breadcrumbs['breadcrumbs'],
        ]);
    }


    public function store(SettingRequest $request)
    {


        $oldSettings = Setting::first();

        DB::beginTransaction();
        try {

            if ($request->hasfile('logo')) {
                if ($oldSettings?->logo != null) {
                    $this->deleteFile($oldSettings->logo, 'logo');
                }
                $logo_name = $this->uploadFile($request, 'logo', 'logo');
            } else {
                $logo_name = $oldSettings->logo ?? '';
            }

            Setting::updateOrCreate(
                [
                    'id' => $oldSettings->id ?? 1,

                ],
                [
                    'name' => [
                        'ar' => $request->name['ar'],
                        'en' => $request->name['en'],
                    ],
                    'address' => [
                        'ar' => $request->address['ar'],
                        'en' => $request->address['en'],
                    ],
   

                    'active' => $request->active,
                    'phone' => $request->phone,
                    'email' => $request->email,

              
                    'weekendDays' => $request->weekendDays,
                    // 'vacationsDates' => $vacationsDates,

                    'logo' => $logo_name,
                    'added_by' => $oldSettings != null ? $oldSettings->added_by : auth()->user()->id,
                    'updated_by' => $oldSettings != null ? auth()->user()->id : null,



                ]
            );



            DB::commit();

            return back()->with('success', 'item updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
    }



}
