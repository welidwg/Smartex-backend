<?php

namespace App\Http\Controllers;

use App\Models\Chaine;
use App\Models\Gamme;
use App\Models\HistoriqueActivite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function equilibrage(Request $request)
    {
        try {
            $gamme = Gamme::where("id", $request->id_gamme)->with(["operations"])->first();
            $chaine = Chaine::where("id", $request->id_chaine)->with("ouvriers")->first();
            $nbr_heure_travail = 8;
            $ouvriersPresents = $chaine->ouvriers->where("present", 1);

            $maxOperationsPerWorker = count($ouvriersPresents) > 30 ? 3 : 2;
            $idReferences = $gamme->operations->pluck('id_reference');
            $uniqueIdReferences = $idReferences->unique();
            $countDistinctIdReferences = $uniqueIdReferences->count();
            $minUsers = ceil($countDistinctIdReferences / 3);

            $operationsCount = $gamme->operations->count();
            $workersNeeded = ceil($operationsCount / $maxOperationsPerWorker);
            if (count($ouvriersPresents) >= 10) {
                $bf = round($gamme->temps / $ouvriersPresents->count(), 3);
                $operations = $gamme->operations;
                $temps_gamme = 0;
                $references_info = [];
                $total_machines = 0;

                //verif besoin machines
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

                //
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
                //calcule de potentiel des ouvriers.
                foreach ($ouvriers as &$ouv) {
                    $potentiel = round(($ouv["allure"] * $bfp) / 100, 3);
                    $ouv["potentiel"] = $potentiel;
                    $ouv["potentiel_min"] = round($potentiel * (1 - 0.1), 3);
                    $ouv["potentiel_max"] = round($potentiel * (1 + 0.1), 3);
                }
                $qte_par_heure = round((($ouvriersPresents->count() * 60) * ($allureG / 100)) / $gamme->temps, 0);
                $qte_par_jour = $qte_par_heure * $nbr_heure_travail;
                $nbr_jours_prevu = round($gamme->quantite / $qte_par_jour, 2);
                $arr = [];
                $machineCount = [];
                $reste = [];
                $reste["operation"] = [];
                $reste["valeur"] = 0;

                //commencement d'équilibrage
                foreach ($ouvriers as $ouvrier) {
                    $competences = [];
                    $top3Comp = [];
                    $comp_i = 0;
                    foreach ($ouvrier["competences"] as $comp) {
                        $comp_i++;
                        if ($comp_i <= 3) {
                            $top3Comp[] = $comp["reference"]["ref"];
                        }
                        # code...
                        if ($comp["score"] != 0)
                            $competences[] = ["machine" => $comp["reference"]["ref"], "score" => $comp["score"]];
                    }
                    $charge = 0;
                    $potentiel = $ouvrier["potentiel_max"];

                    if (!isset($arr[$ouvrier["nom"]])) {
                        $arr[$ouvrier["nom"]] = ["operations" => [], "details" => []];
                    }
                    $nb_machines = 0;
                    $uniqueMachines = [];
                    $nb_opertions = 0;

                    //verifier s'il ya de reste
                    if ($reste["valeur"] != 0) {
                        foreach ($reste["operation"] as $o) {
                            if (!isset($arr[$ouvrier["nom"]]["operations"][$o["machine"]])) {
                                $arr[$ouvrier["nom"]]["operations"] += [$o["machine"] => []];
                            }
                            array_push($arr[$ouvrier["nom"]]["operations"][$o["machine"]], ["operation" => $o["libelle"], "temps" => $reste["valeur"], "machine" => $o["machine"]]);
                        }
                        $charge += round($reste["valeur"], 3);
                        $nb_opertions++;
                        $reste["operation"] = [];
                        $reste["valeur"] = 0;
                    }

                    foreach ($operations as $op) {
                        $operationExiste = false;
                        //verifier si l'opération est déjà accordée
                        foreach ($arr as $k => $v) {
                            foreach ($arr[$k]["operations"] as $ref => $value) {
                                foreach ($arr[$k]["operations"][$ref] as $data) {
                                    if (isset($data["operation"]) && $data["operation"] === $op->libelle) {
                                        $operationExiste = true;
                                        break;
                                    }
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
                        //($potentiel - $charge) > 0.2 && ($potentiel - $op->temps) > 0.2
                        $charged_time = 0;
                        if (($potentiel - $charge) > 0.2) {

                            if (count($arr[$ouvrier["nom"]]["operations"]) >= $maxOperationsPerWorker && !isset($arr[$ouvrier["nom"]]["operations"][$op->reference->ref])) {
                                continue;
                            }
                            $charged_time = $this->handleChargeAndRemainingOperations($op, $charge, $potentiel, $reste);
                            $this->addOperation(
                                $arr,
                                $ouvrier,
                                $op,
                                $charged_time
                            );
                            $nb_opertions++;

                            // if (in_array($op->reference->ref, $top3Comp)) {
                            //     $charged_time = $this->handleChargeAndRemainingOperations($op, $charge, $potentiel, $reste);
                            //     $this->addOperation(
                            //         $arr,
                            //         $ouvrier,
                            //         $op,
                            //         $charged_time
                            //     );
                            //     $nb_opertions++;
                            // } else {
                            //     $give_it = false;
                            //     foreach ($competences as $comp) {
                            //         if ($comp["machine"] === $op->reference->ref && $comp["score"] >= 5) {
                            //             $give_it = true;
                            //             break;
                            //         }
                            //     }

                            //     if ($give_it) {
                            //         $charged_time = $this->handleChargeAndRemainingOperations($op, $charge, $potentiel, $reste);
                            //         $this->addOperation(
                            //             $arr,
                            //             $ouvrier,
                            //             $op,
                            //             $charged_time
                            //         );
                            //         $nb_opertions++;
                            //     } else {
                            //         continue;
                            //     }
                            // }
                        } else {
                            continue;
                        }
                    }
                    $saturation = round($charge * 100 / $potentiel, 3);
                    $ouvrier_machines = 0;
                    foreach ($arr[$ouvrier["nom"]]["operations"] as $ref => $vv) {
                        if (!str_contains($ref, "MAIN")) {
                            $total_machines++;
                            $ouvrier_machines++;
                        }
                    }


                    array_push($arr[$ouvrier["nom"]]["details"], ["charge" => round($charge, 3), "pot" => $potentiel, "sat" => $saturation, "nb_operations" => $nb_opertions, "nb_machines" => $ouvrier_machines, "top3" => $top3Comp]);

                    // $total_machines += count($arr[$ouvrier["nom"]]["operations"]);
                    $charge = 0;
                    if (count($arr[$ouvrier["nom"]]["operations"]) == 0) {
                        unset($arr[$ouvrier["nom"]]);
                    }
                }
                // Compteur global des machines par ouvriers
                $nom = "";
                // $totalMachineCount = [];

                // foreach ($arr as $nomOuvrier => $ops) {
                //     $machinesUniques = [];
                //     foreach ($ops as $operation) {
                //         if (isset($operation['machine'])) {
                //             $machine = $operation['machine'];
                //             if (!in_array($machine, $machinesUniques)) {
                //                 $machinesUniques[] = $machine;
                //                 // Comptez la machine globalement
                //                 if (!isset($totalMachineCount[$machine])) {
                //                     $totalMachineCount[$machine] = 0;
                //                 }
                //                 $totalMachineCount[$machine]++;
                //             }
                //         }
                //     }
                // }
                //total ouvriers
                $total_ouvriers = 0;
                foreach ($arr as $k => $v) {
                    if (count($arr[$k]["operations"]) != 0) {
                        $total_ouvriers++;
                    }
                }

                $summary = [
                    "total_ouvriers" => $total_ouvriers,
                    "eq" => $arr,
                    "type" => "success",
                    "total_machines" => $total_machines,
                    // "reste" => $reste,
                    "refs" => $references_info,
                    "qte" => $gamme->quantite,
                    "temps" => $temps_gamme,
                    "ouvriersDispo" => $ouvriersPresents->count(),
                    "BF" => $bf,
                    "AllureM" => $allureG,
                    "bfp" => $bfp,
                    'qteH' => $qte_par_heure,
                    "qteJ" => $qte_par_jour,
                    "nbJrs" => $nbr_jours_prevu,
                    "ouvriersList" => $ouvriers,
                    "operations" => $operations,
                    "requiredWorkers" => $workersNeeded,
                ];
                HistoriqueActivite::create(["activite" => "Equilibrage de chaîne " . $chaine->libelle, "id_machine" => null, "id_user" => Auth::id()]);

                return response(json_encode($summary), 201);
            }


            return response(json_encode(["type" => "error", "message" => "Nombre ouvriers est insuffisant. Présents :" . count($ouvriersPresents) . " Nécessaires : " . $workersNeeded . ""]), 403);
        } catch (\Throwable $th) {
            return response(json_encode(["type" => "error", "message" => $th->getMessage() . " " . $th->getLine() . " " . json_encode($request->all())]), 501);
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
    function handleChargeAndRemainingOperations($op, &$charge, $potentiel, &$reste)
    {
        if (($op->temps + $charge) <= $potentiel || ($op->temps + $charge) <= $potentiel + 0.2) {
            $charged_time = $op->temps;
            $charge += round($op->temps, 3);
        } else {
            $tmp = $op->temps;
            do {
                $tmp -= 0.1;
            } while (($charge + $tmp) > $potentiel);
            $charge += $tmp;
            array_push($reste["operation"], ["libelle" => $op->libelle, "machine" => $op->reference->ref]);
            $reste["valeur"] = round($tmp, 3);
            $charged_time = round($op->temps - $tmp, 3);
        }
        return $charged_time;
    }


    function addOperation(&$arr, $ouvrier, $op, $charged_time)
    {
        if (!isset($arr[$ouvrier["nom"]]["operations"][$op->reference->ref])) {
            $arr[$ouvrier["nom"]]["operations"] += [$op->reference->ref => []];
        }
        array_push($arr[$ouvrier["nom"]]["operations"][$op->reference->ref], [
            "operation" => $op->libelle,
            "temps" => round($charged_time, 3),
            "machine" => $op->reference->ref
        ]);
    }
}
