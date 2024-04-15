<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GammeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nom' => $this->faker->text(10),
            "temps"=>$this->faker->randomFloat(2,5,20),
            "quantite"=> $this->faker->numberBetween(10,1000),
        ];
    }
}
