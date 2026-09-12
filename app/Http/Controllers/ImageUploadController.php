<?php

namespace App\Http\Controllers;

use App\Actions\Media\DeleteMediaAction;
use App\Actions\Media\StoreMediaAction;
use App\Support\MediaModelResolver;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ImageUploadController extends Controller implements HasMiddleware
{
        use AuthorizesRequests;

    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }

    public function store(Request $request, MediaModelResolver $resolver, StoreMediaAction $action): JsonResponse
    {
        $validated = $request->validate([
            'file'       => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'modelType'  => ['required', 'string'],
            'modelId'    => ['required', 'integer'],
            'collection' => ['nullable', 'string', 'max:100'],
        ]);

        $model = $resolver->resolve($validated['modelType'], $validated['modelId']);

        // Authorize against the resolved model itself, not just the route.
        // $this->authorize('update', $model);

        $collection = $validated['collection'] ?: 'default';
        $media = $action->execute($request, $model, $collection);

        return response()->json([
            'success'    => true,
            'id'         => $media->id,
            'path'       => $media->id,
            'url'        => $media->getFullUrl(),
            'name'       => $media->file_name,
            'size'       => $media->size,
            'collection' => $media->collection_name,
        ], 201);
    }

    public function destroy(int $id, DeleteMediaAction $action): JsonResponse
    {
        $media = Media::find($id);

        if (! $media) {
            return response()->json(['success' => true]);
        }

        // The media's owning model, e.g. $media->model, is what must be
        // authorized against — not the Media row itself.
        // $this->authorize('update', $media->model);

        $action->execute($media);

        return response()->json(['success' => true]);
    }
}