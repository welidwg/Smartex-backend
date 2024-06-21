<?php

namespace Database\Factories;

use App\Models\Chaine;
use App\Models\Ouvrier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class OuvrierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
   
    public function definition()
    {
        //$chaine = Chaine::all()->random();
        $chaine=Chaine::where("libelle","CH16")->first();
        $uniqueMatricule = $this->faker->unique()->numberBetween(1, 10000);
        $existing = DB::table('ouvriers')->where([
            'matricule' => $uniqueMatricule,
        ])->exists();

        while ($existing) {
            $existing = DB::table('ouvriers')->where([
                'matricule' => $uniqueMatricule,
            ])->exists();
        }
        return [
            'nom' => $this->faker->name(),
            "matricule" => $uniqueMatricule,
            "allure" => $this->faker->randomFloat(1, 50, 121),
            "present" => $this->faker->boolean(),
            "id_chaine" => $chaine->id
        ];
    }
}
