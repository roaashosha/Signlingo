<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $i = 0;
        $i++; 
        return [
            "name"=>json_encode([
                "en"=>$this->faker->word(),
                "ar"=>$this->faker->word()
            ]),
            "link"=>"www.$i.com",
            "duration_secs"=>$this->faker->randomDigit()
        ];
    }
}
