<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
