<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
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
            "img"=>"categories\$i.png",
            "desc"=>json_encode([
                "en"=>$this->faker->text(),
                "ar"=>$this->faker->text()
            ])
        ];
    }
}
