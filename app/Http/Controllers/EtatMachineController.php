<?php

namespace App\Http\Controllers;

use App\Models\EtatMachine;
use App\Models\HistoriqueActivite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EtatMachineController extends Controller
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
        return json_encode(EtatMachine::where("libelle", "like", "%$search%")->get());
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
                    EtatMachine::create(["libelle" => $e]);
                }
            } else {
                EtatMachine::create($data);
            }
            HistoriqueActivite::create(["activite" => "Ajout d'état machine " . $etat, "id_machine" => null, "id_user" => Auth::id()]);
            return response(json_encode(["message" => "Etat machine bien ajoutée", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EtatMachine  $etatMachine
     * @return \Illuminate\Http\Response
     */
    public function show(EtatMachine $etatMachine)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EtatMachine  $etatMachine
     * @return \Illuminate\Http\Response
     */
    public function edit(EtatMachine $etatMachine)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EtatMachine  $etatMachine
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EtatMachine $etatMachine)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EtatMachine  $etatMachine
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $req, EtatMachine $etatMachine)
    {
        try {
            $etat = EtatMachine::find($req->id);
            HistoriqueActivite::create(["activite" => "Suppression d'état machine " . $etat->libelle, "id_machine" => null, "id_user" => Auth::id()]);
            $etat->delete();
            return response(json_encode(["message" => "Etat supprimée", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }
}
