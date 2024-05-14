<?php

namespace App\Http\Controllers;

use App\Models\Chaine;
use App\Models\HistoriqueActivite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChaineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        //
        $search = $req->query("search");
        return json_encode(Chaine::where("libelle", "like", "%$search%")->with(["ouvriers.competences.operations.reference", "ouvriers.competences.operations.gamme"])->get());
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
            $data = $request->all();
            $etat = $request->libelle;
            if (str_contains($etat, ",")) {
                $etats = explode(",", $etat);
                foreach ($etats as $e) {
                    Chaine::create(["libelle" => $e]);
                }
            } else {
                Chaine::create($data);
            }
            HistoriqueActivite::create(["activite" => "Création de chaîne " . $etat, "id_machine" => null, "id_user" => Auth::id()]);
            return response(json_encode(["message" => "Chaîne bien créee", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Chaine  $chaine
     * @return \Illuminate\Http\Response
     */
    public function show(Chaine $chaine)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Chaine  $chaine
     * @return \Illuminate\Http\Response
     */
    public function edit(Chaine $chaine)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Chaine  $chaine
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Chaine $chaine)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Chaine  $chaine
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $req, Chaine $chaine)
    {
        try {
            $chaine = Chaine::find($req->id);
            HistoriqueActivite::create(["activite" => "Suppression de la chaîne " . $chaine->libelle . " et toutes les éléments liées avec elle.", "id_machine" => null, "id_user" => Auth::id()]);
            $chaine->delete();
            return response(json_encode(["message" => "Chaîne et toutes éléments liées sont supprimée", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }
}
