<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $guarded = ['id'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function units()
    {
        return $this->belongsToMany(Unit::class, 'answer_units')
            ->withTimestamps();
    }
}
