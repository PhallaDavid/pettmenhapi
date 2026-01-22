<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The guard name for Spatie Permission.
     * This ensures permissions are checked using 'web' guard even when authenticated via Sanctum.
     */
    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'active',
        'phone',
        'address',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'preferences' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    // protected $appends = ['avatar_url'];

    /**
     * Get the user's avatar with full URL.
     *
     * @param  string|null  $value
     * @return string|null
     */
    public function getAvatarAttribute($value)
    {
        return $value ? asset(Storage::url($value)) : null;
    }

    /**
     * Get the full URL for the user's avatar (alias for avatar).
     *
     * @return string|null
     */
    public function getAvatarUrlAttribute()
    {
        return $this->avatar;
    }
}
