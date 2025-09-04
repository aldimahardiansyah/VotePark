<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'site_id',
        'unit_code',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        ];
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdminSite(): bool
    {
        return $this->role === 'admin_site';
    }

    public function isTenant(): bool
    {
        return $this->role === 'tenant';
    }

    public function canAccessSite(Site $site): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        return $this->site_id === $site->id;
    }
}
