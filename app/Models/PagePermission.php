<?php

namespace App\Models;

// use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;


class PagePermission extends Model
{
    use HasFactory;
    use HasTranslations;

    // use Translatable;

    public $translatable = ['name'];



    protected $fillable = [
        'name',
        'permissions',
        'type' // 1 normal 2 special
    ];




    protected function permissions(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    }

    public function added_by_user():BelongsTo
    {
        return $this->belongsTo(User::class , 'added_by')->withTrashed(); 
    }

    public function updated_by_user():BelongsTo
    {
        return $this->belongsTo(User::class , 'updated_by')->withTrashed();
    }

}
