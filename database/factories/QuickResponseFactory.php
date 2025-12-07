<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuickResponse>
 */
class QuickResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "title"=>json_encode([
                "en"=>$this->faker->word(),
                "ar"=>$this->faker->word()
            ]),
            "sound"=>$this->faker->randomDigit().".mp3"
        ];
    }
}
