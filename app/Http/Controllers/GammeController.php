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
        return json_encode(Gamme::with("operations")->get());
    }


    public function equilibrage()
    {
        try {
            $gamme = Gamme::where("id", 2)->with(["operations"])->first();
            $chaine = Chaine::where("libelle", "CH18")->with("ouvriers")->first();
            $nbr_heure_travail = 8;
            $ouvriersPresents = $chaine->ouvriers->where("present", 1);

            if (count($ouvriersPresents) > 10) {
                $bf = round($gamme->temps / $ouvriersPresents->count(), 3);
                $operations = $gamme->operations;
                $temps_gamme = 0;
                $references_info = [];
                foreach ($operations as $operation) {
                    $reference_id = $operation->reference->id;
                    $temps = $operation->temps;
                    $temps_gamme += $temps;
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
                        "competences" => $ouvrier->competences
                    ]);
                }



                $allureG = round($sommeAllure / $ouvriersPresents->count(), 2);
                $bfp = round(($bf / $allureG) * 100, 3);
                foreach ($ouvriers as &$ouv) {
                    $potentiel = round(($ouv["allure"] * $bfp) / 100, 3);
                    $ouv["potentiel"] = $potentiel;
                    $ouv["potentiel_min"] = round($potentiel * (1 - 0.1), 3);
                    $ouv["potentiel_max"] = round($potentiel * (1 + 0.1), 3);
                }
                $qte_par_heure = round((($ouvriersPresents->count() * 60) * ($allureG / 100)) / $gamme->temps, 0);
                $qte_par_jour = $qte_par_heure * $nbr_heure_travail;
                $nbr_jours_prevu = round($gamme->quantite / $qte_par_jour, 2);
                $equilibrage = [];
                $arr = [];
                $machineCount = [];
                $reste = [];
                $reste["operation"] = [];
                $reste["valeur"] = 0;
                foreach ($ouvriers as $ouvrier) {
                    $charge = 0;

                    $potentiel = $ouvrier["potentiel_max"];
                    if (!isset($arr[$ouvrier["nom"]])) {
                        $arr[$ouvrier["nom"]] = [];
                    }
                    //verifier le reste
                    if ($reste["valeur"] != 0) {
                        foreach ($reste["operation"] as $o) {
                            //print_r($o);
                            array_push($arr[$ouvrier["nom"]], ["operation" => $o["libelle"], "temps" => $reste["valeur"], "machine" => $o["machine"]]);
                        }
                        $charge += $reste["valeur"];
                        $reste["operation"] = [];
                        $reste["valeur"] = 0;
                    }
                    foreach ($operations as $op) {
                        $operationExiste = false;
                        //verifier si l'opération est déjà accordée
                        foreach ($arr as $k => $v) {
                            foreach ($arr[$k] as $data) {
                                if (isset($data["operation"]) && $data["operation"] === $op->libelle) {
                                    $operationExiste = true;
                                    break;
                                }
                            }
                            if ($operationExiste) {
                                break;
                            }
                        }
                        if ($operationExiste) {
                            continue;
                        }
                        //fin verif operation

                        //distribution de VT
                        if (($potentiel - $charge) > 0.2) {
                            if (($op->temps + $charge) <= ($potentiel + 0.2)) {
                                $charge += $op->temps;
                            } else {
                                $tmp = $op->temps;
                                do {
                                    $tmp -= 0.1;
                                } while (($charge + $tmp) > $potentiel);
                                $charge += $tmp;
                                array_push($reste["operation"], ["libelle" => $op->libelle, "machine" => $op->reference->ref]);
                                // $reste["operation"] = ["libelle" => $op->libelle, "machine" => $op->reference->ref];
                                $reste["valeur"] = $op->temps - $tmp;
                                $tmp = 0;
                            }

                            array_push($arr[$ouvrier["nom"]], ["operation" => $op->libelle, "temps" => $op->temps, "machine" => $op->reference->ref]);
                        } else {
                            break;
                        }
                    }
                    $saturation = $charge * 100 / $potentiel;
                    array_push($arr[$ouvrier["nom"]], ["charge" => $charge, "pot" => $potentiel, "sat" => $saturation]);

                    $charge = 0;
                }
                $machinesUniques = [];
                $nom = "";
                // foreach ($arr as $nomOuvrier => $ops) {
                //     $nom = $nomOuvrier;
                //     foreach ($ops as $operation) {
                //         if (isset($operation['machine'])) {

                //             $machine = $operation['machine'];
                //             if (!isset($machinesUniques[$machine])) {
                //                 $machinesUniques[$machine] = 0;
                //             }

                //             if ($nom != $nomOuvrier) {
                //                 $machinesUniques[$machine]++;
                //                 $nom = "";
                //             }
                //         }
                //     }
                // }
                $totalMachineCount = []; // Compteur global des machines par ouvriers

                foreach ($arr as $nomOuvrier => $ops) {
                    $machinesUniques = [];
                    foreach ($ops as $operation) {
                        if (isset($operation['machine'])) {
                            $machine = $operation['machine'];
                            if (!in_array($machine, $machinesUniques)) {
                                $machinesUniques[] = $machine;
                                // Comptez la machine globalement
                                if (!isset($totalMachineCount[$machine])) {
                                    $totalMachineCount[$machine] = 0;
                                }
                                $totalMachineCount[$machine]++;
                            }
                        }
                    }
                }

                $summary = [
                    "eq" => $arr,
                    "type" => "success",
                    "reste" => $reste,
                    "test" => $totalMachineCount,
                    "refs" => $references_info,
                    // "qte" => $gamme->quantite,
                    // "temps" => $temps_gamme, "ouvriersDispo" => $ouvriersPresents->count(), "BF" => $bf,
                    // "AllureM" => $allureG, "bfp" => $bfp, 'qteH' => $qte_par_heure,
                    // "qteJ" => $qte_par_jour, "nbJrs" => $nbr_jours_prevu, "ouvriersList" => $ouvriers, "operations" => $operations
                ];
                return response(json_encode($summary), 201);
            }


            return response(json_encode(["type" => "error", "message" => "Aucun ouvrier n'est présent dans cette chaîne ou le nombre des ouvriers est insuffisant."]), 501);
        } catch (\Throwable $th) {
            return response(json_encode(["type" => "error", "message" => $th->getMessage()]), 501);
        }
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
