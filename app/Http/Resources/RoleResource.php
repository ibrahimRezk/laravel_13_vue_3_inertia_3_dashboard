<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->when($this->name, $this->name),

            'slug' => $this->when($this->slug, $this->slug),
            'used_before' => $this->whenNotNull($this->used_before),
            'slug.ar' => $this->whenNotNull($this->getTranslation('slug', 'ar') ?? ''),
            'slug.en' => $this->whenNotNull($this->getTranslation('slug', 'en') ?? ''),

            'created_at_formatted' => $this->when($this->created_at, function () {
                // return $this->created_at->toDayDateTimeString();
                return $this->created_at->isoFormat('Do MMMM YYYY , h:mm a');
            }),
            'permissions' =>$this->whenLoaded('permissions'),
            // 'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'can' => [
                'edit' => $request->user()?->can('edit role'),
                'delete' => $request->user()?->can('delete role') && $this->used_before == false && $this->id != 1,
                'view' => $request->user()?->can('view roles'),
            ],
        ];
    }
}
