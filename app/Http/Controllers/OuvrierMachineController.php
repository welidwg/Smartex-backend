<?php

namespace App\Http\Controllers;

use App\Models\Ouvrier;
use App\Models\OuvrierMachine;
use Illuminate\Http\Request;

class OuvrierMachineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return json_encode(OuvrierMachine::all());
    }
    public function getOuvrierMachineByOuvrierId($id)
    {
        $comp = OuvrierMachine::where("id_ouvrier", $id)->with(["ouvrier", "reference"])->get();
        return json_encode($comp);
    }

    public function getOuvrierMachineByRefId($id)
    {
        $comp = OuvrierMachine::where("id_reference", $id)->with(["ouvrier", "reference"])->get();
        return json_encode($comp);
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
            $ids = $request->ids;
            $id_ouvrier = $request->id_ouvrier;
            foreach ($ids as $id) {
                $check = OuvrierMachine::where("id_ouvrier", $id_ouvrier)->where("id_reference", $id)->first();
                if (!$check) {
                    OuvrierMachine::create(["id_ouvrier" => $id_ouvrier, "id_reference" => $id]);
                }
            }
            OuvrierMachine::whereNotIn("id_reference", $ids)->where("id_ouvrier", $id_ouvrier)->delete();
            return response(json_encode(["type" => "success", "message" => "Compétence bien ajouté"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["type" => "error", "message" => $th->getMessage()]), 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OuvrierMachine  $ouvrierMachine
     * @return \Illuminate\Http\Response
     */
    public function show(OuvrierMachine $ouvrierMachine)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OuvrierMachine  $ouvrierMachine
     * @return \Illuminate\Http\Response
     */
    public function edit(OuvrierMachine $ouvrierMachine)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OuvrierMachine  $ouvrierMachine
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OuvrierMachine $ouvrierMachine)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OuvrierMachine  $ouvrierMachine
     * @return \Illuminate\Http\Response
     */
    public function destroy(OuvrierMachine $ouvrierMachine)
    {
        try {
            $ouvrierMachine->delete();
            return response(json_encode(["type" => "success", "message" => "Compétence bien supprimée"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["type" => "error", "message" => $th->getMessage()]), 200);
        }
    }
}
