<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;
    protected $fillable = ['duration_mins','no_questions','category_id'];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function questions(){
        return $this->hasMany(QuizQuestion::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'quiz_user')
            ->withPivot(['score', 'status', 'time_mins'])
            ->withTimestamps();
    }
}
