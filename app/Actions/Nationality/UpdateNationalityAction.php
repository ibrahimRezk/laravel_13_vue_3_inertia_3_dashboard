<?php

namespace App\Actions\Nationality;

use App\Models\Nationality;
use Illuminate\Support\Facades\DB;

class UpdateNationalityAction
{
    public function execute(Nationality $nationality, array $validated): Nationality
    {
        return DB::transaction(function () use ($nationality, $validated) {
            $nationality->update([
                'name' => [
                    'ar' => $validated['name']['ar'],
                    'en' => $validated['name']['en'],
                ],
                'active'     => (bool) ($validated['active'] ?? false),
                'date'       => now()->toDateString(),
                'updated_by' => auth()->id(),
            ]);

            return $nationality;
        });
    }
}