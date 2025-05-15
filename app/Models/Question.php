<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $guarded = ['id'];

    public function vote()
    {
        return $this->belongsTo(Vote::class);
    }
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
