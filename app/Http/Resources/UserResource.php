<?php

namespace App\Http\Resources;

use App\Models;
use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Resources\AdminResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            // name on curren locale -> use in index
            'name' => $this->when($this->name, $this->name),
            'name.ar' => $this->whenNotNull($this->getTranslation('name', 'ar') ?? ''),
            'name.en' => $this->whenNotNull($this->getTranslation('name', 'en') ?? ''),
            // 'name_ar' => $this->whenNotNull($this->translate('ar')->name ?? ''), // this with return null if database doesn't hav data in arabic
            // 'name_en' => $this->whenNotNull($this->translate('en')->name ?? ''),
            // name in specific lang -> use in create and update          -- don't forget to modify items in index.vue and create.vue
            // 'name_ar' => $this->whenNotNull($this->translate('en')->name), // delete this if database has arabic content and activate above one this will keep showing name_ar and name_en both in english if not changed to ar but if database has no arabic data it will return error in index becase name ar is null


            'active' => $this->whenNotNull($this->active),
            'used_before' => $this->whenNotNull($this->used_before),

            'email' => $this->when($this->email, $this->email),

            'profile' => $this->when($this->profile_id, function () {

                // check profile type then pass the appropirate resource with if else
                if ($this->profile_type == 'App\Models\Admin') {
                    return new AdminResource(Admin::find($this->profile_id));
                }
                //  elseif($this->profile_type == 'App\Models\Admin'){
                //     return new AdminResource(Admin::find($this->profile_id));
                // }
            }),


            'is_email_verified' => $this->when($this->email_verified_at, function () {
                return $this->email_verified_at !== null;
            }),

            'created_at_formatted' => $this->when($this->created_at, function () {
                return $this->created_at->isoFormat('Do MMMM YYYY , h:mm a');
            }),
            'updated_at_formatted' => $this->when($this->updated_at, function () {
                return $this->updated_at->isoFormat('Do MMMM YYYY , h:mm a');
            }),


            'images' => $this->whenLoaded(
                'media',
                fn () => $this->getMedia()->map(      /////////////////// important  to get images collection
                    fn ($media) => [
                        'id' => $media->id,
                        'html' => $media->toHtml(),
                        'img' => $media,
                    ]
                )
            ),
            // 'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'roles' => $this->whenLoaded('roles'),
            'permissions' => $this->whenLoaded('permissions'),


            'can' => [
                'edit' => $request->user()?->can('edit user'),
                'delete' => $request->user()?->can('delete user'),
                'view' => $request->user()?->can('view user'),
            ],

    
        ];
    }
}
