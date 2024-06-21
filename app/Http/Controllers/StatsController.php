<?php

namespace App\Http\Controllers;

use App\Models\Chaine;
use App\Models\Machine;
use App\Models\Ouvrier;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    function initStats()
    {
        //stats des chaînes
        $chainesStats = [];
        $chaines = Chaine::with("ouvriers")->get();
        foreach ($chaines as $chaine) {
            $ouvriers = $chaine->ouvriers;
            $present = $ouvriers->where("present", 1);
            $pourcent = 0;
            if (count($present) != 0)
                $pourcent = count($present) / count($ouvriers) * 100;
            //array_push($chainesStats, [$chaine->libelle => round($pourcent, 2)]);
            array_push($chainesStats, ["libelle" => $chaine->libelle, "percent" => round($pourcent, 2)]);
        }
        usort($chainesStats, function ($a, $b) {
            return $b["percent"] <=> $a["percent"];
        });

        //stats des ouvriers
        $ouvriersStats = [];
        $ouvriers = Ouvrier::with("chaine")->orderBy("allure", "DESC")->limit(5)->get();
        foreach ($ouvriers as $ouvrier) {
            array_push($ouvriersStats, ["nom" => $ouvrier->nom, "allure" => $ouvrier->allure, "chaine" => $ouvrier->chaine->libelle]);
        }

        //stats des predictions
        $machineStats = [];
        $machines = Machine::where("estimation", "!=", null)->get();
        $today = date_create(date("Y-m-d"));
        foreach ($machines as $machine) {
            $estimation = date_create($machine->estimation);
            $interval = date_diff($today, $estimation);
            $inter = -1;
            if ($interval->invert == 0)
                $inter = (int) $interval->format("%a");
            if ($inter < 20 && $inter >= 0 && count($machineStats) < 5)
                // array_push($machineStats, [$machine->code => (int) $interval->format("%a")]);
                array_push($machineStats, ["code" => $machine->code, "interval" => (int) $interval->format("%a")]);
        }

        usort($machineStats, function ($a, $b) {
            return $a["interval"] <=> $b["interval"];
        });

        return json_encode(["chaines" => $chainesStats, "ouvriers" => $ouvriersStats, "preds" => $machineStats]);
    }
}
