<?php

namespace App\Http\Controllers;

use App\Jobs\VerifPanne;
use App\Models\HistoriqueActivite;
use App\Models\HistoriqueMachine;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class HistoriqueMachineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        return json_encode(HistoriqueMachine::where("id_machine", $req->id_machine)->with(["machine", "panne"])->get());
    }

    public function all($idmachine)
    {
        return json_encode(HistoriqueMachine::where("id_machine", $idmachine)->get());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            HistoriqueMachine::create($request->all());
            $this->estimateModel($request->id_machine);
            HistoriqueActivite::create(["activite" => "Ajout d'historique panne", "id_machine" => $request->id_machine, "id_user" => Auth::id()]);
            //dispatch(new VerifPanne());
            return response(json_encode(["message" => "Historique bien ajouté", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HistoriqueMachine  $historiqueMachine
     * @return \Illuminate\Http\Response
     */
    public function show(HistoriqueMachine $historiqueMachine)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HistoriqueMachine  $historiqueMachine
     * @return \Illuminate\Http\Response
     */
    public function edit(HistoriqueMachine $historiqueMachine)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HistoriqueMachine  $historiqueMachine
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HistoriqueMachine $historiqueMachine)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HistoriqueMachine  $historiqueMachine
     * @return \Illuminate\Http\Response
     */
    public function destroy(HistoriqueMachine $historiqueMachine)
    {
        //
    }

    public function estimationAllMachines()
    {
        $machines = Machine::all();
        foreach ($machines as $machine) {
            $check_machine = Machine::find($machine->id);
            if ($check_machine->estimation == null) {

                if ($machine->historique->count() != 0)
                    $this->estimateModel($machine->id);
            }
        }
    }
    public function estimateModel($id_machine)
    {
        try {
            $flaskUrl = 'http://127.0.0.1:5000/predicition/mll';
            $historyRecords = DB::table('historique_machines')
                ->where("id_machine", $id_machine)
                ->orderBy('date_heure')
                ->get();
            $response = Http::post($flaskUrl, ["id_machine" => $id_machine, "history" => $historyRecords]);
            $responseData = $response->json();
            $next_date = $responseData["next"];
            if ($next_date != "") {
                Machine::find($id_machine)->update(["estimation" => $next_date]);
                return response(json_encode(["message" => $next_date, "type" => "success"]), 200);
            }
            return response(json_encode(["message" => "Données insuffisants", "type" => "error"]), 500);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }
    public function getEstimation($id_machine)
    {
        try {


            $historyRecords = DB::table('historique_machines')
                ->where("id_machine", $id_machine)
                ->orderBy('date_heure')
                ->get();
            if ($historyRecords->count() > 1) {

                $periodDifferences = [];
                for ($i = 0; $i < count($historyRecords) - 1; $i++) {
                    $currentDateTime = strtotime($historyRecords[$i]->date_heure);
                    $nextDateTime = strtotime($historyRecords[$i + 1]->date_heure);
                    $periodDifference = $nextDateTime - $currentDateTime;
                    $periodDifferences[] = $periodDifference;
                }
                $averagePeriodDifference = array_sum($periodDifferences) / count($periodDifferences);
                $lastDateTime = strtotime($historyRecords[count($historyRecords) - 1]->date_heure);
                $futureDateTime = $lastDateTime + $averagePeriodDifference;
                $futureDate = date('Y-m-d H:i:s', $futureDateTime);
                $days = floor($averagePeriodDifference / (60 * 60 * 24));
                $hours = floor(($averagePeriodDifference % (60 * 60 * 24)) / (60 * 60));
                $minutes = floor(($averagePeriodDifference % (60 * 60)) / 60);
                $seconds = $averagePeriodDifference % 60;

                // Créer un objet DateInterval avec les composantes calculées
                $dateInterval = new \DateInterval("P{$days}DT{$hours}H{$minutes}M{$seconds}S");
                return json_encode(["estimated" => $futureDate, "avg" => $dateInterval->format('%d jours'), "last_date" => $historyRecords[count($historyRecords) - 1]->date_heure, "type" => "success"]);
            }
            return response(json_encode(["message" => "Aucun historique", "type" => "error"]), 500);
            sleep(2);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }
}
