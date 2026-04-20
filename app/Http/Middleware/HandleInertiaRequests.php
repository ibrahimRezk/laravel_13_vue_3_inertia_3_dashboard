<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'token' => csrf_token(),
            ],

           
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
             'messages' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'vital_error' => $request->session()->get('vital_error'),
            ],
       


        
            // 'itemIdToBeOpened' => $request->session()->get('itemIdToBeOpened'),
            // 'itemIdToBePrinted' => $request->session()->get('itemIdToBePrinted'),
            // 'locale' => App::getLocale(),


            
            'paginationNumber' => 10,

            'menus' => [
                [
                    'title' => 'Dashboard',
                    'href' => route('dashboard'),
                    'isActive' => $request->routeIs('dashboard'),
                    'isVisible' => $request->user()?->can('view dashboard'),
                ],
                [
                    'title' => 'Nationalities',
                    'href' => route('nationalities.index'),
                    'isActive' => $request->routeIs('nationalities.index'),
                    'isVisible' => $request->user()?->can('view nationalities'),
                ],
                [
                    'title' => 'General Settings',
                    'isActive' => $request->routeIs(['systemSettings.*']),
                    'isVisible' => $request->user()?->can('view system settings'),

                    'hasSubmenu' => true,
                    'open' => false,
                    'subMenus' => [
                        [
                            'title' => 'system settings',
                            'href' => route('systemSettings.index'),
                            'isActive' => $request->routeIs('systemSettings.index'),
                            'isVisible' => $request->user()?->can('view system settings'),
                        ],

                    ]
                ],
                [
                    'title' => 'Admins & Permissions',
                    'isActive' =>
                        $request->routeIs('admins.*')
                        or $request->routeIs('roles.*')
                    ,

                     'isVisible' => 
                    $request->user()?->can('view admins') 
                    || $request->user()?->can('view roles'),
                    'hasSubmenu' => true,
                    'open' => false,
                    'subMenus' => [
                        [
                            'title' => 'System Admins',
                            'href' => route('admins.index'),
                            'isActive' => $request->routeIs('admins.*'),
                            'isVisible' => $request->user()?->can('view admins'),
                            
                        ],
                        [
                            'title' => 'Roles and Permissions',
                            'href' => route('roles.index'),
                            'isActive' => $request->routeIs('roles.*'),
                            'isVisible' => $request->user()?->can('view roles'),
                        ],
                    ],
                ],
            ],

        ];
    }
}
