<?php

namespace Database\Factories;

use App\Models\Gamme;
use App\Models\Reference;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $ref = Reference::all()->random();
        $gamme = Gamme::all()->random();
        return [
            'libelle' => $this->faker->text(100),
            "id_reference" => $ref->id,
            "id_gamme" => $gamme->id,
            "temps" => $this->faker->randomFloat(3, 0.1, 1.6),
        ];
    }
}
