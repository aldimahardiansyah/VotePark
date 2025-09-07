<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'address',
        'phone',
        'email',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function adminUsers(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'admin_site');
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'tenant');
    }
}
