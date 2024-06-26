<?php

namespace App\Http\Controllers;

use App\Models\HistoriqueActivite;
use App\Models\Reference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferencesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        $search = $req->query("search");
        return json_encode(Reference::where("ref", "like", "%$search%")->with("machines.reference", "machines.etat", "machines.chaine", "machines.echanges", "machines.historique", "machines.echanges.chaineTo", "machines.echanges.chaineFrom", "competences")->get());
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
            $ref = $request->ref;
            if (str_contains($ref, ",")) {
                $refs = explode(",", $ref);
                foreach ($refs as $r) {
                    Reference::create(["ref" => $r]);
                }
            } else {
                Reference::create($data);
            }

            HistoriqueActivite::create(["activite" => "Ajout de référence " . $ref, "id_machine" => null, "id_user" => Auth::id()]);

            return response(json_encode(["message" => "Référence bien créee", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $ref = Reference::find($id);
            HistoriqueActivite::create(["activite" => "Modification de référence " . $ref->ref . " > " . $request->ref, "id_machine" => null, "id_user" => Auth::id()]);
            $ref->update($request->all());
            return response(json_encode(["message" => "Référence modifiée", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $ref = Reference::find($id);
            HistoriqueActivite::create(["activite" => "Suppression référence " . $ref->ref, "id_machine" => null, "id_user" => Auth::id()]);
            $ref->delete();
            return response(json_encode(["message" => "Référence supprimée", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }
}
