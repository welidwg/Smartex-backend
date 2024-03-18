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
        $startDateTime = '-5 years';
        $endDateTime = 'now';
        $startDate = $this->faker->dateTimeBetween($startDateTime, $endDateTime);

        // Génération de l'heure aléatoire entre 06:00:00 et 18:00:00
        $startTime = $this->faker->time('H:i:s', '06:00:00');
        $endTime = $this->faker->time('H:i:s', '18:00:00');

        // Combinaison de la date et de l'heure
        $dateTime = $startDate->format('Y-m-d') . ' ' . $startTime;
        return [
            'id_machine' => $machine->id,
            'date_heure' => $dateTime,
            'historique' => $this->faker->realText(50)
        ];
    }
}
