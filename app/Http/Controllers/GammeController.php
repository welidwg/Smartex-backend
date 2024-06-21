<?php

namespace App\Http\Controllers;

use App\Models\Chaine;
use App\Models\Gamme;
use App\Models\HistoriqueActivite;
use App\Models\Operation;
use App\Models\OuvrierMachine;
use App\Models\Reference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function equiPoly(Request $req)
    {
        $reference = Reference::with([
            'competences' => function ($query) {
                $query->with([
                    'ouvrier' => function ($query) {
                        $query->without('competences');
                    }
                ])->without('reference')->select("score", "id_ouvrier", "id_reference");
            }
        ])->select("id", "ref")->get();
        $gamme = Gamme::where("id", 3)->with(["operations"])->first();
        $chaine = Chaine::where("libelle", "CH18")->with("ouvriers")->first();
        $nbr_heure_travail = 8;
        $ouvriersPresents = $chaine->ouvriers->where("present", 1);

        //calcule des formules

        //base de fragemntation
        $bf = round($gamme->temps / $ouvriersPresents->count(), 3);

        //total des allures et détails des ouvriers
        $sommeAllure = 0;
        $ouvriers = [];
        foreach ($ouvriersPresents as $ouvrier) {
            $sommeAllure += $ouvrier->allure;
        }
        //allure moyenne
        $allureG = round($sommeAllure / $ouvriersPresents->count(), 2);

        //base de fragemntation pondérée
        $bfp = round(($bf / $allureG) * 100, 3);
        $qte_par_heure = round((($ouvriersPresents->count() * 60) * ($allureG / 100)) / $gamme->temps, 0);
        $qte_par_jour = $qte_par_heure * $nbr_heure_travail;
        $nbr_jours_prevu = round($gamme->quantite / $qte_par_jour, 2);
        //calcule de potentiel des ouvriers.
        foreach ($ouvriers as &$ouv) {
            $potentiel = round(($ouv["allure"] * $bfp) / 100, 3);
            $ouv["potentiel"] = $potentiel;
            $ouv["potentiel_min"] = round($potentiel * (1 - 0.1), 3);
            $ouv["potentiel_max"] = round($potentiel * (1 + 0.1), 3);
        }

        //équilibrage
        $equi = [];
        //le tableau de reste des opérations
        $reste = [];
        $reste["operation"] = [];
        $reste["valeur"] = 0;
        $operations = $gamme->operations;
        $total_machines = 0;

        foreach ($operations as $op) {
            $OM = OuvrierMachine::where("id_reference", $op->id_reference)->where("score", "!=", 0)->with("ouvrier")->orderBy("score", "DESC")->get();

            if ($reste["valeur"] != 0) {
                $ref = '';
                foreach ($reste["operation"] as $o) {
                    $oper = Operation::find($o["id"]);
                    $ouvs = OuvrierMachine::where("id_reference", $oper->id_reference)->where("score", "!=", 0)->with("ouvrier")->orderBy("score", "DESC")->get();
                    foreach ($ouvs as $ouv) {
                        $ouvrier = $ouv->ouvrier;
                        $potentiel = round(($ouvrier["allure"] * $bfp) / 100, 3);

                        if (!$ouvrier->present)
                            continue;
                        if (!isset($equi[$ouvrier->nom])) {

                            $equi[$ouvrier->nom] = ["operations" => [], "details" => [], "charge" => 0, "pot" => $potentiel];
                        } elseif (count($equi[$ouvrier->nom]["operations"]) >= 3 && !isset($equi[$ouvrier->nom]["operations"][$o["machine"]])) {
                            continue;
                        }
                        if (($potentiel - $equi[$ouvrier->nom]["charge"]) < 0.2)
                            continue;
                        if (!isset($equi[$ouvrier->nom]["operations"][$o["machine"]])) {
                            $equi[$ouvrier->nom]["operations"] += [$o["machine"] => []];
                        }

                        array_push($equi[$ouvrier->nom]["operations"][$o["machine"]], ["id" => $o["id"], "operation" => $o["libelle"], "temps" => $reste["valeur"], "machine" => $o["machine"], "linked" => true]);
                        $equi[$ouvrier->nom]["charge"] += round($reste["valeur"], 3);
                        break;
                    }
                }
                // $nb_opertions++;
                $reste["operation"] = [];
                $reste["valeur"] = 0;
            }
            $operationExiste = false;
            foreach ($equi as $k => $v) {
                foreach ($equi[$k]["operations"] as $ref => $value) {
                    foreach ($equi[$k]["operations"][$ref] as $data) {
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

            foreach ($OM as $ouvrierMachine) {
                $ouvrier = $ouvrierMachine->ouvrier;
                $potentiel = round(($ouvrier["allure"] * $bfp) / 100, 3);

                //verifier présence ouvrier
                if (!$ouvrier->present)
                    continue;
                if (!isset($equi[$ouvrier->nom])) {
                    $equi[$ouvrier->nom] = ["operations" => [], "details" => [], "charge" => 0, "pot" => $potentiel];
                    $continue = false;
                } elseif (count($equi[$ouvrier->nom]["operations"]) >= 3 && !isset($equi[$ouvrier->nom]["operations"][$op->reference->ref])) {
                    continue;
                }

                $charge = $equi[$ouvrier->nom]["charge"];
                $charged_time = 0;
                if (($potentiel - $charge) > 0.2) {
                    $charged_time = $this->processing($op, $ouvrier, $reste, $equi, $potentiel);
                    $this->addOperation(
                        $equi,
                        $ouvrier,
                        $op,
                        $charged_time
                    );

                    break;
                    //$nb_opertions++;
                } else {
                    continue;
                }
            }
        }
        foreach ($equi as $ouvrier => $value) {
            $saturation = round($equi[$ouvrier]["charge"] * 100 / $equi[$ouvrier]["pot"], 3);
            foreach ($equi[$ouvrier]["operations"] as $ref => $vv) {
                if (!str_contains($ref, "MAIN")) {
                }
                $total_machines++;
            }
            $equi[$ouvrier]["saturation"] = $saturation;
        }
        $summary = [
            //"total_ouvriers" => $total_ouvriers,
            "eq" => $equi,
            "type" => "success",
            "total_machines" => $total_machines,
            // "reste" => $reste,
            //"refs" => $references_info,
            "qte" => $gamme->quantite,
            "temps" => $gamme->temps,
            "ouvriersDispo" => $ouvriersPresents->count(),
            "BF" => $bf,
            "AllureM" => $allureG,
            "bfp" => $bfp,
            'qteH' => $qte_par_heure,
            "qteJ" => $qte_par_jour,
            "nbJrs" => $nbr_jours_prevu,
            //"ouvriersList" => $ouvriers,
            //"operations" => $operations,
            //"requiredWorkers" => $workersNeeded,
        ];
        //return json_encode(["eq" => $equi, "workers" => count($equi), "machines" => $total_machines]);
        return json_encode($summary);
    }
    public function equilibrage(Request $request)
    {
        try {
            $gamme = Gamme::where("id", $request->id_gamme)->with(["operations"])->first();
            $chaine = Chaine::where("id", $request->id_chaine)->with("ouvriers")->first();
            //$refs = Reference::all();
            $nbr_heure_travail = 8;
            $ouvriersPresents = $chaine->ouvriers->where("present", 1);

            $maxOperationsPerWorker = count($ouvriersPresents) > 30 ? 3 : 2;
            $idReferences = $gamme->operations->pluck('id_reference');
            $uniqueIdReferences = $idReferences;
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
                    $potentiel = $ouvrier["potentiel"];

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


                            $give_it = false;
                            foreach ($competences as $comp) {
                                if ($comp["machine"] === $op->reference->ref && $comp["score"] >= 5) {
                                    $give_it = true;
                                    break;
                                }
                            }
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
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response(json_encode(["type" => "error", "message" =>  json_last_error_msg()]), 403);
                }
                return response(json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 201);
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
    function processing($op, $ouvrier, &$reste, &$equi, $potentiel)
    {
        $charge = $equi[$ouvrier["nom"]]["charge"];
        if (($op->temps + $charge) <= $potentiel || ($op->temps + $charge) <= $potentiel + 0.2) {
            $charged_time = $op->temps;
            $charge += round($op->temps, 3);
        } else {
            $tmp = $op->temps;
            do {
                $tmp -= 0.1;
            } while (($charge + $tmp) > $potentiel);
            $charge += $tmp;
            array_push($reste["operation"], ["libelle" => $op->libelle, "machine" => $op->reference->ref, "id" => $op->id]);
            $reste["valeur"] = round($tmp, 3);
            $charged_time = round($op->temps - $tmp, 3);
        }
        $equi[$ouvrier["nom"]]["charge"] = $charge;
        return $charged_time;
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
            "id" => $op->id,
            "operation" => $op->libelle,
            "temps" => round($charged_time, 3),
            "machine" => $op->reference->ref
        ]);
    }
    function addOperationPoly(&$arr, $nomOuvrier, $op, $charged_time)
    {
        if (!isset($arr[$nomOuvrier]["operations"][$op->reference->ref])) {
            $arr[$nomOuvrier]["operations"] += [$op->reference->ref => []];
        }
        array_push($arr[$nomOuvrier]["operations"][$op->reference->ref], [
            "operation" => $op->libelle,
            "temps" => round($charged_time, 3),
            "machine" => $op->reference->ref
        ]);
        $arr[$nomOuvrier]["charge"] += $charged_time;
    }
}
