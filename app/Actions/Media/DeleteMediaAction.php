<?php

namespace App\Actions\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DeleteMediaAction
{
    public function execute(Media $media): void
    {
        $media->delete();
    }
}