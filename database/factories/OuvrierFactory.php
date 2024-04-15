<?php

namespace Database\Factories;

use App\Models\Chaine;
use App\Models\Ouvrier;
use Illuminate\Database\Eloquent\Factories\Factory;

class OuvrierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
   
    public function definition()
    {
        $chaine = Chaine::all()->random();
        $uniqueMatricule = $this->faker->unique()->numberBetween(1, 1000);
        return [
            'nom' => $this->faker->name(),
            "matricule" => $uniqueMatricule,
            "allure" => $this->faker->randomFloat(1, 50, 121),
            "present" => $this->faker->boolean(),
            "id_chaine" => $chaine->id
        ];
    }
}
