<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $guarded = ['id'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_unit')->withTimestamps();
    }

    public function owners()
    {
        return $this->belongsToMany(User::class, 'user_unit')->withTimestamps();
    }

    // For backward compatibility, get the primary owner
    public function user()
    {
        return $this->belongsToMany(User::class, 'user_unit')->withTimestamps()->first();
    }

    public function event()
    {
        return $this->belongsToMany(Event::class, 'event_unit')->withPivot('unit_code');
    }

    public function answers()
    {
        return $this->belongsToMany(Answer::class);
    }
}
