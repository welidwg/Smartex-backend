<?php

namespace App\Http\Controllers;

use App\Models\HistoriqueActivite;
use App\Models\Ouvrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OuvrierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        $chaine = $req->query("chaine");
        $search = $req->query("search");
        if ($chaine != 0) {
            return json_encode(Ouvrier::where("nom", "like", "%$search%")->where("id_chaine", $chaine)->with('chaine')->get());
        } else {
            return json_encode(Ouvrier::where("nom", "like", "%$search%")->orWhere("matricule", "like", "%$search%")->with('chaine')->get());
        }
    }
    public function markPresenceAuto(Request $req)
    {
        try {
            $ouvriers = Ouvrier::where("id_chaine", $req->id_chaine)->get();

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
            return response(json_encode(["message" => "Présence bien marquée", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }
    public function markPresence(Request $req)
    {
        try {
            $ids = $req->ids;
            $chaine = "";
            $chaine_id = 0;
            foreach ($ids as $id) {
                $ouv = Ouvrier::where("id", $id)->with("chaine")->first();
                $ouv->update(["present" => 1]);
                if ($chaine == "") {
                    $chaine = $ouv->chaine->libelle;
                }
                if ($chaine_id == 0) {
                    $chaine_id = $ouv->chaine->id;
                }
            }
            if (count($ids) != 0) {
                Ouvrier::whereNotIn("id", $ids)->where("id_chaine", $chaine_id)->update(["present" => 0]);
                HistoriqueActivite::create(["activite" => "Marquer la présence des ouvriers du chaîne $chaine", "id_machine" => null, "id_user" => Auth::id()]);
            }
            return response(json_encode(["message" => "Présence bien marquée", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
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
        try {
            // $validator = Validator::make($request->all(), Utilisateur::$rules, Utilisateur::$messages);
            // if ($validator->fails()) {
            //     return json_encode([
            //         'type' => "error",
            //         'message' => $validator->errors()
            //     ]);
            // }

            $data = $request->all();
            $new = Ouvrier::create($data);
            HistoriqueActivite::create(["activite" => "Ajout ouvrier " . $new->nom, "id_machine" => null, "id_user" => Auth::id()]);
            return response(json_encode(["message" => "Ouvrier bien ajouté", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ouvrier  $ouvrier
     * @return \Illuminate\Http\Response
     */
    public function show(Ouvrier $ouvrier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Ouvrier  $ouvrier
     * @return \Illuminate\Http\Response
     */
    public function edit(Ouvrier $ouvrier)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ouvrier  $ouvrier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ouvrier $ouvrier)
    {
        try {

            // $rules = Utilisateur::$rules;
            // $rules['username'] = $rules['username'] . ',username,' . $id;
            // $validator = Validator::make($request->all(), $rules, Utilisateur::$messages);
            // if ($validator->fails()) {
            //     return json_encode([
            //         'type' => "error",
            //         'message' => $validator->errors()
            //     ]);
            // }
            $data = $request->all();
            $ouvrier->update($data);
            HistoriqueActivite::create(["activite" => "Modification d'ouvrier " . $ouvrier->nom, "id_machine" => null, "id_user" => Auth::id()]);
            return response(json_encode(["message" => "Ouvrier bien modifié", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ouvrier  $ouvrier
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ouvrier $ouvrier)
    {
        //
        try {
            HistoriqueActivite::create(["activite" => "Suppression ouvrier " . $ouvrier->nom, "id_machine" => null, "id_user" => Auth::id()]);
            $ouvrier->delete();
            return response(json_encode(["message" => "Ouvrier bien supprimé", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }
}
