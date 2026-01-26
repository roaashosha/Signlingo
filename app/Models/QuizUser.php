<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizUser extends Model
{
    use HasFactory;
    protected $table = 'quiz_user';
    protected $fillable = [
    'user_id',
    'quiz_id',
    'score',
    'time_mins',
    'status',
    'start_time',
];

public function quiz(){
    return $this->belongsTo(Quiz::class);
}
}
