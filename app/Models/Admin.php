<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'added_by',
        'updated_by',
    ];

    public function user()
    {
        return $this->morphOne(User::class, 'profile')->withTrashed();
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
