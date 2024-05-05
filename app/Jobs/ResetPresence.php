<?php

namespace App\Jobs;

use App\Http\Controllers\OuvrierController;
use App\Models\Chaine;
use App\Models\Ouvrier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResetPresence implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        try {
            //Ouvrier::query()->update(['present' => 0]);
            $ctrl = new OuvrierController();
            $chaine = Chaine::where("libelle", "CH18")->first();
            try {
                $ouvriers = Ouvrier::where("id_chaine", $chaine->id)->get();
                $totalOuvriers = count($ouvriers);
                $minPresenceOne = 10;

                for ($i = 0; $i < min($minPresenceOne, $totalOuvriers); $i++) {
                    $ouvrier = $ouvriers[$i];
                    $ouvrier->update(["present" => 1]);
                }

                for ($i = $minPresenceOne; $i < $totalOuvriers; $i++) {
                    $ouvrier = $ouvriers[$i];
                    $ouvrier->update(["present" => mt_rand(0, 1)]);
                }
                echo "done";
            } catch (\Throwable $th) {
                echo $th->getMessage();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
