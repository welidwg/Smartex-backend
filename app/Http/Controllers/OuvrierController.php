<?php

namespace App\Http\Controllers;

use App\Models\HistoriqueActivite;
use App\Models\Ouvrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OuvrierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        $chaine = $req->query("chaine");
        $search = $req->query("search");
        if ($chaine != 0) {
            return json_encode(Ouvrier::where("nom", "like", "%$search%")->where("id_chaine", $chaine)->with('chaine')->get());
        } else {
            return json_encode(Ouvrier::where("nom", "like", "%$search%")->orWhere("matricule", "like", "%$search%")->with('chaine')->get());
        }
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
            // $validator = Validator::make($request->all(), Utilisateur::$rules, Utilisateur::$messages);
            // if ($validator->fails()) {
            //     return json_encode([
            //         'type' => "error",
            //         'message' => $validator->errors()
            //     ]);
            // }

            $data = $request->all();
            $new = Ouvrier::create($data);
            HistoriqueActivite::create(["activite" => "Ajout ouvrier " . $new->nom, "id_machine" => null, "id_user" => Auth::id()]);
            return response(json_encode(["message" => "Ouvrier bien ajouté", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ouvrier  $ouvrier
     * @return \Illuminate\Http\Response
     */
    public function show(Ouvrier $ouvrier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Ouvrier  $ouvrier
     * @return \Illuminate\Http\Response
     */
    public function edit(Ouvrier $ouvrier)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ouvrier  $ouvrier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ouvrier $ouvrier)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ouvrier  $ouvrier
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ouvrier $ouvrier)
    {
        //
    }
}
