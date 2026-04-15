<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;


#[Fillable(['name', 'email', 'password' , 'avatar','active','used_before',])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable  implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    use HasRoles;
    use SoftDeletes;  
    use InteractsWithMedia;
    use HasTranslations;


            public $translatable = ['name'];

              public function profile()
    {
        return $this->morphTo();
    }
  
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            
            'password' => 'hashed',
            'active' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
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
