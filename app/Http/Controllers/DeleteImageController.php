<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DeleteImageController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('can:edit role');
    // }


    public function __invoke( $id )
    {
        $media =   Media::find($id);
        $media->delete();
        // return back()->with('success', 'image deleted successfully'); 
        return response(['status' => true], 200);

    }
}
