<?php

namespace Database\Factories;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistoriqueMachineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $machine = Machine::all()->random();
        return [
            'id_machine' => $machine->id,
            'date_heure' => $this->faker->dateTime(),
            'historique' => $this->faker->text
            //
        ];
    }
}
