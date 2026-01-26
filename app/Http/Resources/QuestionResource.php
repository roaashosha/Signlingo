<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "title"=>$this->title,
            "media"=>$this->media,
            "option1"=>$this->option_1,
            "option2"=>$this->option_2,
            "option3"=>$this->option_3,
            "option4"=>$this->option_4,
            "answer"=>$this->answer,
            "quiz_id"=>$this->quiz_id
        ];
    }
}
