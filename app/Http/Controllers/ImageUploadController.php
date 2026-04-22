<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ImageUploadController extends Controller
{

    /**
     * Handle a single-file upload via Dropzone and attach it to a model
     * using Spatie MediaLibrary.
     *
     * Expected multipart fields:
     *   file       — the image binary (Dropzone sends one file per request)
     *   modelType  — fully-qualified class name or short alias, e.g. "App\Models\Product"
     *   modelId    — the model's primary key
     *   collection — (optional) media collection name, defaults to "default"
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file'       => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:5120'],
            'modelType'  => ['required', 'string'],
            'modelId'    => ['required', 'integer'],
            'collection' => ['nullable', 'string', 'max:100'],
        ]);

        // ── Resolve the model ─────────────────────────────────────────────────
        $modelClass = $this->resolveModelClass($request->input('modelType'));
        $modelId    = (int) $request->input('modelId');
        $collection = $request->input('collection') ?: 'default';

        // Ensure the class exists and implements HasMedia
        if (! class_exists($modelClass)) {
            return response()->json(['message' => "Model class [{$modelClass}] not found."], 422);
        }

        if (! in_array(HasMedia::class, class_implements($modelClass) ?: [])) {
            return response()->json(['message' => "Model [{$modelClass}] does not implement HasMedia."], 422);
        }

        /** @var Model&HasMedia $model */
        $model = $modelClass::findOrFail($modelId);

        // ── Enforce the 10-image cap server-side per collection ───────────────
        $maxFiles = 10;
        $current  = $model->getMedia($collection)->count();

        if ($current >= $maxFiles) {
            return response()->json([
                'message' => "Maximum {$maxFiles} images allowed in collection '{$collection}'.",
            ], 422);
        }

        // ── Add the file to the media library ─────────────────────────────────
        $media = $model
            ->addMediaFromRequest('file')
            ->usingFileName($this->sanitizeFilename(
                $request->file('file')->getClientOriginalName()
            ))
            ->toMediaCollection($collection);

        // ── Return the response the Vue component expects ─────────────────────
        // Note: "path" carries the Spatie Media ID so destroy() can find it.
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

    /**
     * Delete a media item by its Spatie Media ID.
     *
     * Expected JSON body:
     *   path — the Spatie Media ID returned by store() as an integer
     */
    public function destroy($id): JsonResponse
    {
     


        /** @var Media|null $media */
        $media = Media::find($id);


        if (! $media) {
            // Already deleted or never existed — treat as success (idempotent)
            return response()->json(['success' => true]);
        }

        // Optional: uncomment to restrict deletion to the authed user's own models
        // $this->authorizeMediaDeletion($media);

        $media->delete(); // Spatie removes the file from disk automatically

        return response()->json(['success' => true]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Map the modelType string from the frontend to a safe, fully-qualified
     * Eloquent class name.
     *
     * Security: extend $allowlist with short aliases to avoid exposing raw
     * class names in HTTP requests (recommended for production).
     *
     *   'product' => \App\Models\Product::class,
     *   'article' => \App\Models\Article::class,
     *
     * If no alias matches, we fall back to accepting any App\Models\* class.
     */
    private function resolveModelClass(string $modelType): string
    {
        // ── Option A: short alias allowlist (recommended) ─────────────────────
        $allowlist = [
            // 'product' => \App\Models\Product::class,
            // 'article' => \App\Models\Article::class,
        ];

        if (array_key_exists($modelType, $allowlist)) {
            return $allowlist[$modelType];
        }

        // ── Option B: fully-qualified class names scoped to App\Models\ ───────
        if (str_starts_with($modelType, 'App\\Models\\')) {
            return $modelType;
        }

        abort(422, "Invalid modelType [{$modelType}].");
    }

    /**
     * Strip path-traversal characters and unsafe bytes from a filename.
     */
    private function sanitizeFilename(string $name): string
    {
        $name = preg_replace('/[\/\\\0]/', '', $name); // no slashes or null bytes
        $name = ltrim($name, '.');                      // no leading dots
        return preg_replace('/\s+/', '_', $name) ?: 'upload';
    }
}
