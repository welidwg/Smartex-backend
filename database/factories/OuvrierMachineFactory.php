<?php

namespace Database\Factories;

use App\Models\Ouvrier;
use App\Models\Reference;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class OuvrierMachineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    function getRandomFloat($min, $max)
    {
        // Generate a random integer within the range [min * 2, max * 2]
        $randomInt = mt_rand($min * 2, $max * 2);
        // Divide by 2 to get either a whole number or a .5 step
        return $randomInt / 2;
    }
    public function definition()
    {
        $ouvrier = Ouvrier::all()->random();
        $ref = Reference::all()->random();
        $existing = DB::table('ouvrier_machines')->where([
            'id_ouvrier' => $ouvrier->id,
            'id_reference' => $ref->id,
        ])->exists();
        while ($existing) {
            $ouvrier = Ouvrier::all()->random();
            $ref = Reference::all()->random();

            $existing = DB::table('ouvrier_machines')->where([
                'id_ouvrier' => $ouvrier->id,
                'id_reference' => $ref->id,
            ])->exists();
        }
        return [
            "id_ouvrier" => $ouvrier->id,
            "id_reference" => $ref->id,
            "score" => $this->getRandomFloat(0, 10)
        ];
    }
}
