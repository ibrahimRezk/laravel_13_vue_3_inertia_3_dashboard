<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Relations\Relation;

class UploadImagesController extends Controller
{

    // public function __construct()
    // {
    //     $this->middleware('can:edit role');
    // }


    public function __invoke(Request $request)
    {

        // $modelClass = Relation::getMorphedModel($request->modelType); // we need it only if we modify appServiceProvider like below
        $modelClass = $request->modelType;

        $validation = Validator::make(
            $request->all(),
            [
                'image' => ['bail', 'required', 'image', 'max:1024'],

                'modelType' => ['bail', 'required', 'string'
                // , Rule::in(array_keys(Relation::morphMap())) //// we activate this only if we did it in app service provicer we force it to look inside Relation and apply new modifications witch hashed in app service proveder 
            ],

                'modelId' => [
                    'bail', 'required', 'integer', Rule::exists($modelClass, 'id'),
                ],
            ],
            /// messages
            [
                'max' => 'الصورة حجمها كبير'

            ]
        );
        if ($validation->fails()) {
            return response($validation->errors()->first(), 400);
        }

        // uploade the image

        /** @var \Spatie\MediaLibrary\InteractsWithMedia */

        $model = $modelClass::find($request->modelId);
        // dd($model);
        

       $image =  $model->addMediaFromRequest('image')   /// model id and model type will come from payload request
            // ->withResponsiveImages()  // for uploading multiple sizes of the same files  but it take too much time on saving
            ->toMediaCollection();  // for uploading multiple images within one request

        // return response('success');

        return response(['status' => true, 'image' => $image], 200); 

        
    }
}
