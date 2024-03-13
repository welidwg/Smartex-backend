<?php

namespace App\Http\Controllers;

use App\Models\HistoriqueMachine;
use Illuminate\Http\Request;

class HistoriqueMachineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        return json_encode(HistoriqueMachine::where("id_machine", $req->id_machine)->with("machine")->all());
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
            HistoriqueMachine::create($request->all());
            return response(json_encode(["message" => "Historique bien ajouté", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HistoriqueMachine  $historiqueMachine
     * @return \Illuminate\Http\Response
     */
    public function show(HistoriqueMachine $historiqueMachine)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HistoriqueMachine  $historiqueMachine
     * @return \Illuminate\Http\Response
     */
    public function edit(HistoriqueMachine $historiqueMachine)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HistoriqueMachine  $historiqueMachine
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HistoriqueMachine $historiqueMachine)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HistoriqueMachine  $historiqueMachine
     * @return \Illuminate\Http\Response
     */
    public function destroy(HistoriqueMachine $historiqueMachine)
    {
        //
    }
}
