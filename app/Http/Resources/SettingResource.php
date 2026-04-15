<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // 'id' => $this->id,
            'id' => $this->whenNotNull($this->id),
            // 'set_batches_settings' => $this->set_batches_settings,
            // 'batches_type_settings' => $this->batches_type_settings,

              'name' => $this->when($this->name, $this->name),
    'name.ar' => $this->whenNotNull($this->getTranslation('name', 'ar') ?? ''),
    'name.en' => $this->whenNotNull($this->getTranslation('name', 'en') ?? ''),
    'address' => $this->when($this->address, $this->address),
    'address.ar' => $this->whenNotNull($this->getTranslation('address', 'ar') ?? ''),
    'address.en' => $this->whenNotNull($this->getTranslation('address', 'en') ?? ''),



            'active' => $this->whenNotNull($this->active),
            'phone' => $this->whenNotNull($this->phone),
            'email' => $this->whenNotNull($this->email),

            'weekendDays' => $this->whenNotNull($this->weekendDays),
            // 'vacationsDates' => $this->whenNotNull($this->vacationsDates),
            'logo' => $this->whenNotNull($this->logo),


      'added_by_user' =>  new UserResource($this->whenLoaded('added_by_user')) ?? '',
     'updated_by_user' => new UserResource($this->whenLoaded('updated_by_user')) ??  '',

            'created_at_formatted' => $this->when($this->created_at, function () {
                // return $this->created_at->toDayDateTimeString();
                return $this->created_at->isoFormat('Do MMMM YYYY , h:mm a');
            }),

            // 'updated_at_formatted' => $this->when($this->updated_at, function () {
            //     return $this->updated_at->diffForHUMANS() ;
            // }),

            'updated_at_formatted' => $this->when($this->updated_at, function () {
                return $this->updated_at->isoFormat('Do MMMM YYYY , h:mm a');
            }),

            'can' => [
                'edit' => $request->user()->can('edit settings'),
            ],
        ];
    }
}
