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
        $gamme = Gamme::where("id", 2)->with("operations.competences")->first();
        $chaine = Chaine::where("libelle", "CH18")->with("ouvriers")->first();
        $nbr_heure_travail = 8;
        $ouvriersPresents = $chaine->ouvriers->where("present", 1);

        if (count($ouvriersPresents) > 0 && count($ouvriersPresents) > 10) {
            $operations = $gamme->operations;
            $bf = round($gamme->temps / $ouvriersPresents->count(), 3);
            $sommeAllure = 0;
            $ouvriers = [];
            foreach ($ouvriersPresents as $ouvrier) {
                $sommeAllure += $ouvrier->allure;
                array_push($ouvriers,   [
                    "nom" => $ouvrier->nom, "matricule" => $ouvrier->matricule, "allure" => $ouvrier->allure,
                    "competences" => $ouvrier->competences->where("operations.id_gamme", $gamme->id)
                ]);
            }



            $allureG = $sommeAllure / $ouvriersPresents->count();
            $bfp = round(($bf / $allureG) * 100, 3);
            foreach ($ouvriers as &$ouv) {
                $potentiel = round(($ouv["allure"] / $allureG) * 100, 3);
                $ouv["potentiel"] = $potentiel;
            }
            $qte_par_heure = round((($ouvriersPresents->count() * 60) * ($allureG / 100)) / $gamme->temps, 0);
            $qte_par_jour = $qte_par_heure * $nbr_heure_travail;
            $nbr_jours_prevu = round($gamme->quantite / $qte_par_jour, 2);
            $summary = [
                "type" => "success",
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
