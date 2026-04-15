<?php

namespace App\Models;

// use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole; 
use Spatie\Translatable\HasTranslations;



class Role extends SpatieRole
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'roles';
    
    public $translatable = ['slug'];

    protected $fillable = [
        'name' ,
        'guard_name' ,
        'slug',
        'used_before',
        ] ;
        
        protected $casts = [
            'used_before' => 'boolean',
        ];

}
