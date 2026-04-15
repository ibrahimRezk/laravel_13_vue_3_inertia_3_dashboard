<?php

namespace App\Http\Resources;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed> 
     */
    public function toArray(Request $request): array
    {

        
        $can_edit = auth()->user()->can('edit admin');
        $can_delete = auth()->user()->can('delete admin');

        return [
            'id' => $this->id,

            'added_by_user' =>  new UserResource($this->whenLoaded('added_by_user')) ?? '',
            'updated_by_user' => new UserResource($this->whenLoaded('updated_by_user')) ??  '',

            'phone' => $this->whenNotNull($this->phone),


            'can' => [
                'edit' => $can_edit,
                'delete' => $can_delete,
            ],
        ];

    }
}
