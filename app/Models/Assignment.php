<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = ["teacher_id", "title", "file_path"];
    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
    public function teacher()
    {
        return $this->belongsTo(User::class, "teacher_id");
    }
}
