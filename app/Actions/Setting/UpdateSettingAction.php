<?php

namespace App\Actions\Setting;

use App\Http\Traits\AttachFilesTrait;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpdateSettingAction
{
    use AttachFilesTrait;

    public function execute(Request $request, array $validated): Setting
    {
        return DB::transaction(function () use ($request, $validated) {
            $oldSettings = Setting::first();

            $logoName = $oldSettings->logo ?? null;

            if ($request->hasFile('logo')) {
                if ($oldSettings?->logo) {
                    $this->deleteFile($oldSettings->logo, 'logo');
                }
                $logoName = $this->uploadFile($request, 'logo', 'logo');
            }

            return Setting::updateOrCreate(
                ['id' => $oldSettings->id ?? 1],
                [
                    'name' => [
                        'ar' => $validated['name']['ar'],
                        'en' => $validated['name']['en'],
                    ],
                    'address' => [
                        'ar' => $validated['address']['ar'],
                        'en' => $validated['address']['en'],
                    ],
                    'active'      => (bool) ($validated['active'] ?? false),
                    'phone'       => $validated['phone'],
                    'email'       => $validated['email'],
                    'weekendDays' => $validated['weekendDays'],
                    'logo'        => $logoName,
                    'added_by'    => $oldSettings->added_by ?? auth()->id(),
                    'updated_by'  => $oldSettings ? auth()->id() : null,
                ]
            );
        });
    }
}