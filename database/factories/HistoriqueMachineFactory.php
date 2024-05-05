<?php

namespace Database\Factories;

use App\Models\Machine;
use App\Models\PanneMachine;
use App\Models\Role;
use App\Models\Utilisateur;
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
        $panne = PanneMachine::all()->random();
        $adminID = Role::where("role", "Admin")->first()->id;
        $techID = Role::where("role", "Technicien")->first()->id;
        $user = Utilisateur::whereIn("role", [$adminID, $techID])->get()->random();
        $startDateTime = '-3 years';
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
            "id_panne" => $panne->id,
            "added_by" => null
        ];
    }
}
