<?php

namespace App\Http\Controllers;

use App\Models\Chaine;
use App\Models\Gamme;
use Illuminate\Http\Request;

class GammeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return json_encode(Gamme::with("operations.competences")->get());
    }
    public function equilibrage()
    {
        $gamme = Gamme::where("id", 2)->with(["operations.competences"])->first();
        $chaine = Chaine::where("libelle", "CH18")->with("ouvriers")->first();
        $nbr_heure_travail = 8;
        $ouvriersPresents = $chaine->ouvriers->where("present", 1);

        if (count($ouvriersPresents) > 0 && count($ouvriersPresents) > 10) {
            $bf = round($gamme->temps / $ouvriersPresents->count(), 3);
            $operations = $gamme->operations;
            $refs = [];
            $references_info = [];
            foreach ($operations as $operation) {
                $reference_id = $operation->reference->id;
                $temps = $operation->temps;
                if (!isset($references_info[$reference_id])) {
                    $references_info[$reference_id] = [
                        'ref' => $operation->reference->ref,
                        'count' => 0,
                        'total_temps' => 0,
                        'besoin' => 0
                    ];
                }
                $references_info[$reference_id]['count']++;
                $references_info[$reference_id]['total_temps'] += $temps;
            }
            foreach ($references_info as $key => $value) {
                $references_info[$key]['total_temps'] = round($references_info[$key]['total_temps'], 4);
                $references_info[$key]["besoin"] = ceil($references_info[$key]["total_temps"] / $bf);
            }

            $sommeAllure = 0;
            $ouvriers = [];
            foreach ($ouvriersPresents as $ouvrier) {
                $sommeAllure += $ouvrier->allure;
                array_push($ouvriers,   [
                    "nom" => $ouvrier->nom, "matricule" => $ouvrier->matricule, "allure" => $ouvrier->allure,
                    //"competences" => $ouvrier->competences->where("operations.id_gamme", $gamme->id)
                ]);
            }



            $allureG = round($sommeAllure / $ouvriersPresents->count(), 2);
            $bfp = round(($bf / $allureG) * 100, 3);
            foreach ($ouvriers as &$ouv) {
                $potentiel = ceil(($ouv["allure"] / $allureG) * 100);
                $ouv["potentiel"] = $potentiel;
                $ouv["potentiel_min"] = ceil($potentiel * (1 - 0.1));
                $ouv["potentiel_max"] = ceil($potentiel * (1 + 0.1));
            }
            $qte_par_heure = round((($ouvriersPresents->count() * 60) * ($allureG / 100)) / $gamme->temps, 0);
            $qte_par_jour = $qte_par_heure * $nbr_heure_travail;
            $nbr_jours_prevu = round($gamme->quantite / $qte_par_jour, 2);
            $summary = [
                "type" => "success",
                "refs" => $references_info,
                "qte" => $gamme->quantite,
                "temps" => $gamme->temps, "ouvriersDispo" => $ouvriersPresents->count(), "BF" => $bf,
                "AllureM" => $allureG, "bfp" => $bfp, 'qteH' => $qte_par_heure,
                "qteJ" => $qte_par_jour, "nbJrs" => $nbr_jours_prevu, "ouvriersList" => $ouvriers, "operations" => $operations
            ];
            return response(json_encode($summary), 201);
        }


        return response(json_encode(["type" => "error", "message" => "Aucun ouvrier n'est présent dans cette chaîne ou le nombre des ouvriers est insuffisant."]), 501);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Gamme  $gamme
     * @return \Illuminate\Http\Response
     */
    public function show(Gamme $gamme)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Gamme  $gamme
     * @return \Illuminate\Http\Response
     */
    public function edit(Gamme $gamme)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Gamme  $gamme
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Gamme $gamme)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Gamme  $gamme
     * @return \Illuminate\Http\Response
     */
    public function destroy(Gamme $gamme)
    {
        //
    }
}
