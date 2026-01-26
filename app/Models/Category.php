<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['name','desc','img'];

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function quiz(){
        return $this->hasOne(Quiz::class);
    }
    public function scopeFilter($query,$name){
        if ($name){
            if ($name) {
                $query->where('name', 'LIKE', "%{$name}%");
            }
            return $query;
        }
    }
}
