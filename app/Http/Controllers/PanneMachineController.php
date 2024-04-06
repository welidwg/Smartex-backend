<?php

namespace App\Http\Controllers;

use App\Models\PanneMachine;
use Illuminate\Http\Request;

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
        //
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
    public function destroy(PanneMachine $panneMachine)
    {
        //
    }
}
