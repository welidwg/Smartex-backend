<?php

namespace App\Http\Controllers;

use App\Models\HistoriqueActivite;
use App\Models\PanneMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanneMachineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return json_encode(PanneMachine::all());
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
            $type = $request->libelle;
            if (str_contains($type, ",")) {
                $types = explode(",", $type);
                foreach ($types as $r) {
                    PanneMachine::create(["libelle" => $r]);
                }
            } else {
                PanneMachine::create($data);
            }
            HistoriqueActivite::create(["activite" => "Ajout de type panne " . $type, "id_machine" => null, "id_user" => Auth::id()]);
            return response(json_encode(["message" => "Type de panne bien crée", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PanneMachine  $panneMachine
     * @return \Illuminate\Http\Response
     */
    public function show(PanneMachine $panneMachine)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PanneMachine  $panneMachine
     * @return \Illuminate\Http\Response
     */
    public function edit(PanneMachine $panneMachine)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PanneMachine  $panneMachine
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PanneMachine $panneMachine)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PanneMachine  $panneMachine
     * @return \Illuminate\Http\Response
     */
    public function destroy(PanneMachine $panneMachine, $id)
    {
        try {
            $pn = PanneMachine::find($id);
            //HistoriqueActivite::create(["activite" => "Suppression de type panne " . $panneMachine->libelle, "id_machine" => null, "id_user" => Auth::id()]);
            $pn->delete();
            return response(json_encode(["message" => "Type panne supprimé" . $panneMachine->id, "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }
}
