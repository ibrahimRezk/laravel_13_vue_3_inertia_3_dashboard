<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MediaModelResolver
{
    /**
     * Explicit alias => class allowlist. No fallback to arbitrary
     * App\Models\* classes — every model that can receive uploads
     * through this endpoint must be listed here on purpose.
     */
    private const ALLOWED_MODELS = [
        // 'product' => \App\Models\Product::class,
        'user'    => \App\Models\User::class,

        // 'article' => \App\Models\Article::class,
    ];

    public function resolve(string $alias, int $id): Model
    {
        if (! array_key_exists($alias, self::ALLOWED_MODELS)) {
            throw new HttpException(422, "Invalid modelType [{$alias}].");
        }

        $modelClass = self::ALLOWED_MODELS[$alias];

        if (! in_array(HasMedia::class, class_implements($modelClass) ?: [])) {
            throw new HttpException(422, "Model [{$modelClass}] does not implement HasMedia.");
        }

        return $modelClass::findOrFail($id);
    }
}