<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PagePermissionResource extends JsonResource
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
            'name.ar' => $this->whenNotNull($this->getTranslation('name', 'ar') ?? ''),
            'name.en' => $this->whenNotNull($this->getTranslation('name', 'en') ?? ''),

            'type ' => $this->whenNotNull($this->type),

            'permissions' => $this->whenNotNull($this->permissions),

 
        ];
    }
}
