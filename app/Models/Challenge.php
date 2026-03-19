<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    protected $fillable = ["teacher_id", "hint"];
    public function teacher()
    {
        return $this->belongsTo(User::class, "teacher_id");
    }
}
