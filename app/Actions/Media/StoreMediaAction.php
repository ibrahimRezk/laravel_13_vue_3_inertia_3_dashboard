<?php

namespace App\Actions\Media;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StoreMediaAction
{
    private const MAX_FILES_PER_COLLECTION = 10;

    public function execute(Request $request, Model $model, string $collection): Media
    {
        $current = $model->getMedia($collection)->count();

        if ($current >= self::MAX_FILES_PER_COLLECTION) {
            throw new HttpException(422, "Maximum " . self::MAX_FILES_PER_COLLECTION . " images allowed in collection '{$collection}'.");
        }

        return $model
            ->addMediaFromRequest('file')
            ->usingFileName($this->sanitizeFilename(
                $request->file('file')->getClientOriginalName()
            ))
            ->toMediaCollection($collection);
    }

    private function sanitizeFilename(string $name): string
    {
        $name = preg_replace('/[\/\\\0]/', '', $name);
        $name = ltrim($name, '.');

        return preg_replace('/\s+/', '_', $name) ?: 'upload';
    }
}