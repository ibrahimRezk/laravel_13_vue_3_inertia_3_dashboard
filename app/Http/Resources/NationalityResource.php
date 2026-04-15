<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NationalityResource extends JsonResource
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



            'active' => $this->whenNotNull($this->active),
            'date' => $this->whenNotNull($this->date),

            'added_by_user' => new UserResource($this->whenLoaded('added_by_user')) ?? '',
            'updated_by_user' => new UserResource($this->whenLoaded('updated_by_user')) ?? '',

            'created_at_formatted' => $this->when($this->created_at, function () {
                return $this->created_at->isoFormat('Do MMMM YYYY , h:mm a');
            }),

            'updated_at_formatted' => $this->when($this->updated_at, function () {
                return $this->updated_at->isoFormat('Do MMMM YYYY , h:mm a');
            }),
            'used_before' => $this->whenNotNull($this->used_before),


            'can' => [
                // 'edit' => $request->user()->can('edit nationality'),
                // 'delete' => $request->user()->can('delete nationality') && $this->used_before == false,
                'edit' => true,
                'delete' => true,
            ],
        ];
    }
}
