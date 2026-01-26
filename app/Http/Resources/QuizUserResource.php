<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "user_quiz_id"=>$this->id,
            "duration_mins"=>$this->quiz->duration_mins,
            "no_questions"=>$this->quiz->no_questions
        ];
    }
}
