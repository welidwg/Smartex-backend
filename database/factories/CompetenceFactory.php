<?php

namespace Database\Factories;

use App\Models\Operation;
use App\Models\Ouvrier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Constraint\Operator;

class CompetenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $ouvrier = Ouvrier::all()->random();
        $operation = Operation::all()->random();
        $existing = DB::table('competences')->where([
            'id_ouvrier' => $ouvrier->id,
            'id_operation' => $operation->id,
        ])->exists();
        while ($existing) {
            $ouvrier = Ouvrier::all()->random();
            $operation = Operation::all()->random();

            $existing = DB::table('competences')->where([
                'id_ouvrier' => $ouvrier->id,
                'id_operation' => $operation->id,
            ])->exists();
        }
        return [
            "id_ouvrier" => $ouvrier->id,
            "id_operation" => $operation->id,
            "competence" => $this->faker->randomFloat(0, 10, 100),
        ];
    }
}
