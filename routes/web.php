<?php

use App\Http\Controllers\DeleteImageController;
use App\Http\Controllers\UploadImagesController;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttachPermissionToRoleController;
use App\Http\Controllers\DetachPermissionFromRoleController;
use App\Http\Controllers\NationalityController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SystemSettingController;
use Inertia\Inertia;

use App\Http\Controllers\ImageUploadController;


Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});



// remove axios and add new method from solar system
/// check composer file and add new packages
/// check package.json and add new packages
/// check input-otp
/// check :header


Route::middleware([
    'Lang',
    'auth',
    // 'verified',
])->group(function () {

    Route::get('dashboard', function () {

    $user = User::first();
    $user->load('media');

        return Inertia::render('Dashboard' , [
            'item' => new UserResource($user)
        ]);
    })->name('dashboard');
    


    Route::resource('systemSettings', SystemSettingController::class)->only(['index' , 'store']);
    Route::resource('nationalities', NationalityController::class);
    
    
    
    Route::resource('admins', AdminController::class)->parameters(['admins' => 'user']);
    Route::resource('roles', RolesController::class)->only(['index' , 'create' , 'store' , 'edit' , 'update' , 'destroy']);
    Route::post('roles/attach-permission', AttachPermissionToRoleController::class)->name('roles.attach-permission');
    Route::post('roles/detach-permission', DetachPermissionFromRoleController::class)->name('roles.detach-permission');
    
    
    
    
    
    
    
    // Route::post('upload-image', UploadImagesController::class)->name('image.store');
    // Route::delete('delete-image/{id}', DeleteImageController::class)->name('image.delete');


Route::post('/upload-image', [ImageUploadController::class, 'store'])->name('image.store');
Route::delete('/delete-image/{id}', [ImageUploadController::class, 'destroy'])->name('image.destroy');








       //////////// to change lang /////////
       Route::get('/change_lang/{locale}', function ($locale) {
        // dd('hi');
        App::setLocale($locale);
        session()->put('lang', $locale);
        return redirect()->back();
    })->name('lang');

});





require __DIR__.'/settings.php';



// breadcrumb multiple stage , darkmode delete modal

///////////////////////////////////////////////////////////////////////////////////////
// check pos and hrms userresource this line :
//                 'show' => $request->user()?->can('show user'),
// has to be
//                 'view' => $request->user()?->can('view user'),
// in pos and hrms fix this :
// in appserviceprovider
// $superAdmin = Role::first();
        
//         Gate::before(function ($user, $ability)use($superAdmin) {
//             return $user->hasRole($superAdmin->name) ? true : null;
//             // return $user->hasRole('Super Admin') ? true : null;
//         });

// in role create.vue:
// <!-- v-if="edit && props.item.name != 'Super Admin'" -->
//             <Permissions
//                 v-if="edit && props.item.id != 1"
//                 :item="item"
//                 :pagesPermissions="props.pagesPermissions"
//             />



// add this line to create role afterAll(function () {
    //...watch(()=>  props.edit , ()=> (props.edit ? fillForm(props.item) : ''))

// });


///////////////////////////////////////////////////////////////////////////////////////


// importan   on github inside selectgroup.vue    fix  this => import Select from "@/Components/Select.vue";    to "vue"
 

// there is a separator in sidebarmenuitem.vue

// ليعمل النظام بشكل سليم قم اولا بتفعيل هذا الجزء 
  // wayfinder({
        //     formVariants: true,
        // }),
        // في  vit.config.ts
        // ثم قم بعمل
        // php artisan wayfinder:generate --with-form 
        //  ثم قم بإلغاء تفعيل الجزء الأول مرة أخرى
        // ثم قم بعمل npm run dev or npm run build