<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsToMany(Event::class, 'event_unit')
            ->withPivot('unit_code', 'status', 'registered_email')
            ->withTimestamps();
    }

    public function answers()
    {
        return $this->belongsToMany(Answer::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
