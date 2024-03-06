<?php

namespace App\Http\Controllers;

use App\Models\EtatMachine;
use Illuminate\Http\Request;

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
        //
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
    public function destroy(EtatMachine $etatMachine)
    {
        //
    }
}
