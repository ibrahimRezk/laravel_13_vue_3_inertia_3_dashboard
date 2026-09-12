<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\SettingRequest;
use App\Http\Resources\SettingResource;
use App\Actions\Setting\UpdateSettingAction;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SystemSettingController extends Controller implements HasMiddleware
{
    private string $routeResourceName = 'settings';

    public static function middleware(): array
    {
        return [
            new Middleware('can:view system settings', only: ['index']),
            new Middleware('can:edit system settings', only: ['store']),
        ];
    }

    public function index(Request $request): Response
    {
        $settings = Setting::first();

        return Inertia::render('GeneralSettings/Settings/Index', [
            'title'    => 'System Settings',
            'item'     => $settings ? new SettingResource($settings) : null,
            'logoPath' => $settings?->logo ? asset('attachments/logo/' . $settings->logo) : '',
            'can' => [
                'edit' => $request->user()->can('edit settings'),
            ],
        ]);
    }

    public function store(SettingRequest $request, UpdateSettingAction $action): RedirectResponse
    {
        $action->execute($request, $request->validated());

        return back()->with('success', 'item updated successfully');
    }
}