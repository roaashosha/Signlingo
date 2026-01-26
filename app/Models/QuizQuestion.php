<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;
    protected $guarded = ['created_at','updated_at','id'];

    public function quiz(){
        return $this->belongsTo(Quiz::class);
    }
}
