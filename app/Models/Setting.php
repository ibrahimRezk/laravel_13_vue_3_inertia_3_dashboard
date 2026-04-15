<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Casts\Attribute;

// use Astrotomic\Translatable\Translatable;

class Setting extends Model  implements HasMedia
{
    use HasFactory;
    use HasTranslations;
    use InteractsWithMedia;

    // use Translatable;
    public $translatable = ['name' , 'address' , 'currency'];

    public $with = ['media' , 'added_by_user','updated_by_user'];

    // public $translatedAttributes = ['name', 'address', 'currency'];
    // public $with = ['translations', 'media'];

    protected $fillable = [
        'name',
        'address',
        'active',
        'phone',
        'email',
        'added_by',
        'updated_by',
        'weekendDays',
        'logo',
    ];

    protected $casts = [
        'weekendDays' => 'array',
        'active' => 'boolean',
        // 'vacationsDates' => 'array',
    ];

    // protected $attributes = [
    //     'weekendDays' => [],
    // ];

    protected function weekendDays(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true), // true menas it is associative array
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
