<?php

namespace App\Models;

use App\Enums\AppUserRole;
use App\Traits\HasActiveColumn;
use App\Traits\HasUuidWithlog;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable implements FilamentUser, JWTSubject
{
    use HasUuidWithlog, HasActiveColumn,
     HasApiTokens, HasFactory,
      Notifiable, HasRoles,
      SoftDeletes;


    protected $fillable = [
        'name',
        'email',
        'password',
        'country_code',
        'phone_number',
        'avatar_url',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->hasAnyRole(AppUserRole::DahboardRoles());
    }

    public function getFilamentAvatarUrl(): ?string
    {

        if (empty($this->avatar_url)) {
            return null;
        }

        // return Storage::url($this->avatar_url);
        return Storage::temporaryUrl($this->avatar_url, now()->addMinutes(5));
    }
}
