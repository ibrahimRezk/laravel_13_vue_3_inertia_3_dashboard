<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;

trait AttachFilesTrait
{
    public function uploadFile($request, $name, $folder)
    {
        $image_name = $request->file($name)->hashName();
        $request->file($name)->storeAs('attachments/', $folder . '/' . $image_name, 'attachments');
        return $image_name;
    }

    public function deleteFile($name, $folder)
    {
        $exists = Storage::disk('attachments')->exists('attachments/'.$folder.'/' . $name);

        if ($exists) {
            Storage::disk('attachments')->delete('attachments/'.$folder.'/' . $name); 
        }
    }
}
