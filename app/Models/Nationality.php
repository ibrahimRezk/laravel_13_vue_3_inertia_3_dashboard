<?php

namespace App\Models;

// use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;


class Nationality extends Model
{
    use HasFactory;
    use HasTranslations;


    public $translatable = ['name'];




    protected $fillable = [
        'name',
        'added_by',
        'updated_by',
        'date',
        'active',
        'used_before',
    ];

    protected $casts = [
        'active' => 'boolean',
        'used_before' => 'boolean',
    ];
 

    public function added_by_user():BelongsTo
    {
        return $this->belongsTo(User::class , 'added_by')->withTrashed();
    }

    public function updated_by_user():BelongsTo
    {
        return $this->belongsTo(User::class , 'updated_by')->withTrashed();
    }
    public function scopeActive($builder)
    {
        return $builder->where('active', true);
    }

    public function scopeInActive($builder)
    {
        return $builder->where('active', false);
    }
}
