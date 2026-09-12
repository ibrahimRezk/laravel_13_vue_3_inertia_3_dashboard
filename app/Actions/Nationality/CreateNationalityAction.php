<?php

namespace App\Actions\Nationality;

use App\Models\Nationality;
use Illuminate\Support\Facades\DB;

class CreateNationalityAction
{
    public function execute(array $validated): Nationality
    {
        return DB::transaction(function () use ($validated) {
            return Nationality::create([
                'name' => [
                    'ar' => $validated['name']['ar'],
                    'en' => $validated['name']['en'],
                ],
                'active'   => (bool) ($validated['active'] ?? false),
                'date'     => now()->toDateString(),
                'added_by' => auth()->id(),
            ]);
        });
    }
}