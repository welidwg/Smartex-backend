<?php

namespace App\Jobs;

use App\Events\NewNotificationSent;
use App\Models\Machine;
use App\Models\Notification;
use App\Models\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifPanne implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $machines = Machine::all();

            foreach ($machines as $machine) {

                $estimation = $machine->estimation;
                if ($estimation != null) {
                    $futureDate = $estimation;
                    $today = strtotime(date('Y-m-d'));
                    $$today = strtotime(date('Y-m-d'));
                    $tomorrow = strtotime('+1 day', $today);
                    $afterTomorrow = strtotime('+2 day', $today);
                    $fut = date_create($futureDate);
                    $tod = date_create(date("Y-m-d"));
                    $diff = date_diff($tod, $fut);
                    $diffEnJours = $diff->format('%r%a');
                    if (strtotime($futureDate) == $today || strtotime($futureDate) == $tomorrow || strtotime($futureDate) == $afterTomorrow || ($diffEnJours > 0 &&  $diffEnJours < 7)) {
                        $time = strtotime($futureDate) == $today ? "aujourd'hui" : "dans $diffEnJours jours";
                        $content = "La machine " . $machine->code . " peut tomber en panne $time.";
                        $adminId = Role::where("role", "Admin")->first()->id;
                        broadcast(new NewNotificationSent(Notification::create(["title" => "Panne machine", "content" => $content, "to_role" => $adminId])))->toOthers();
                    }
                }
                //$periodDifferences = [];
                // $id = $machine->id;
                // $historyRecords = DB::table('historique_machines')
                // ->where("id_machine", $id)
                // ->orderBy('date_heure')
                // ->get();
                // if ($historyRecords->count() > 0) {
                //     for ($i = 0; $i < count($historyRecords) - 1; $i++) {
                //         $currentDateTime = strtotime($historyRecords[$i]->date_heure);
                //         $nextDateTime = strtotime($historyRecords[$i + 1]->date_heure);
                //         $periodDifference = $nextDateTime - $currentDateTime;
                //         $periodDifferences[] = $periodDifference;
                //     }
                //     $averagePeriodDifference = array_sum($periodDifferences) / count($periodDifferences);
                //     $lastDateTime = strtotime($historyRecords[count($historyRecords) - 1]->date_heure);
                //     $futureDateTime = $lastDateTime + $averagePeriodDifference;
                //     $futureDate = date('Y-m-d', $futureDateTime);
                //     $today = strtotime(date('Y-m-d'));
                //     $tomorrow = strtotime('+1 day', $today);
                //     $afterTomorrow = strtotime('+2 day', $today);
                //     $fut = date_create($futureDate);
                //     $tod = date_create(date("Y-m-d"));
                //     $diff = date_diff($tod, $fut);
                //     $diffEnJours = $diff->format('%r%a');
                //     // Extraire la différence en jours
                //     if (strtotime($futureDate) == $today || strtotime($futureDate) == $tomorrow || strtotime($futureDate) == $afterTomorrow || ($diffEnJours > 0 &&  $diffEnJours < 7)) {
                //         echo $diffEnJours . " / ";
                //         $time = strtotime($futureDate) == $today ? "aujourd'hui" : "dans $diffEnJours jours";
                //         $content = "La machine " . $machine->code . " peut tomber en panne $time.";
                //         $adminId = Role::where("role", "Admin")->first()->id;
                //         broadcast(new NewNotificationSent(Notification::create(["title" => "Panne machine", "content" => $content, "to_role" => $adminId])))->toOthers();
                //     }
                // }
            }
        } catch (\Throwable $th) {
            error_log($th);
            echo $th;
        }
    }
}
