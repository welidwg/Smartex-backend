<?php

namespace App\Http\Controllers;

use App\Models\HistoriqueActivite;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MachineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        $search = $req->query("search");
        return json_encode(Machine::where("code", "like", "%$search%")->with('historiqueActivite')->get());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
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
            $ma = Machine::create($request->all());
            HistoriqueActivite::create(["activite" => "Ajout de machine", "id_machine" => $ma->id, "id_user" => Auth::id()]);
            return response(json_encode(["message" => "Machine bien créee", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Machine  $machine
     * @return \Illuminate\Http\Response
     */
    public function show(Machine $machine)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Machine  $machine
     * @return \Illuminate\Http\Response
     */
    public function edit(Machine $machine)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Machine  $machine
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Machine $machine)
    {
        try {
            $machine->update($request->all());
            HistoriqueActivite::create(["activite" => "Modification de machine", "id_machine" => $machine->id, "id_user" => Auth::id()]);

            return response(json_encode(["message" => "Machine modifiée", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Machine  $machine
     * @return \Illuminate\Http\Response
     */
    public function destroy(Machine $machine)
    {
        try {
            HistoriqueActivite::create(["activite" => "Suppression de machine " . $machine->code . "/" . $machine->chaine->libelle, "id_machine" => null, "id_user" => Auth::id()]);
            $machine->delete();
            return response(json_encode(["message" => "Machine supprimée", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }
}
