<?php

namespace App\Actions\Nationality;

use App\Models\Nationality;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DeleteNationalitiesAction
{
    /**
     * @param  int[]  $ids
     */
    public function execute(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            $nationalities = Nationality::whereIn('id', $ids)->get();

            $blocked = $nationalities->firstWhere('used_before', true);

            if ($blocked) {
                throw new HttpException(403, "\"{$blocked->name}\" has been used before and cannot be deleted.");
            }

            Nationality::whereIn('id', $ids)->delete();
        });
    }
}