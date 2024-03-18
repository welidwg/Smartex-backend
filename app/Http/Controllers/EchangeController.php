<?php

namespace App\Http\Controllers;

use App\Models\Echange;
use Illuminate\Http\Request;

class EchangeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {

        return json_encode(Echange::where("id_machine", $req->id_machine)->get());
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
            Echange::create($request->all());
            return response(json_encode(["message" => "Echange bien ajouté", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Echange  $echange
     * @return \Illuminate\Http\Response
     */
    public function show(Echange $echange)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Echange  $echange
     * @return \Illuminate\Http\Response
     */
    public function edit(Echange $echange)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Echange  $echange
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Echange $echange)
    {
        try {
            $echange->update(["isActive" => false]);
            return response(json_encode(["message" => "Echange est bien annulé", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Echange  $echange
     * @return \Illuminate\Http\Response
     */
    public function destroy(Echange $echange)
    {
        //
    }
}
