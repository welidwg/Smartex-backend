<?php

namespace App\Http\Controllers;

use App\Models\HistoriqueActivite;
use Illuminate\Http\Request;

class HistoriqueActiviteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        if ($req->has("id_machine")) {
            return json_encode(HistoriqueActivite::where("id_machine", $req->id_machine)->get());
        }
        return json_encode(HistoriqueActivite::where("id_user", $req->id_user)->get());
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
     * @param  \App\Models\HistoriqueActivite  $historiqueActivite
     * @return \Illuminate\Http\Response
     */
    public function show(HistoriqueActivite $historiqueActivite)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HistoriqueActivite  $historiqueActivite
     * @return \Illuminate\Http\Response
     */
    public function edit(HistoriqueActivite $historiqueActivite)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HistoriqueActivite  $historiqueActivite
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HistoriqueActivite $historiqueActivite)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HistoriqueActivite  $historiqueActivite
     * @return \Illuminate\Http\Response
     */
    public function destroy(HistoriqueActivite $historiqueActivite)
    {
        //
    }
}
