<?php

namespace App\Http\Controllers;

use App\Events\NewNotificationSent;
use App\Models\EtatMachine;
use App\Models\HistoriqueActivite;
use App\Models\Machine;
use App\Models\Notification;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
            $validator = Validator::make($request->all(), Machine::$rules, Machine::$messages);
            if ($validator->fails()) {
                return json_encode([
                    'type' => "error",
                    'message' => $validator->errors()
                ]);
            }
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
        return json_encode($machine);
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
            $current_etat = $machine->etat->libelle;
            $check_etat = EtatMachine::find($request->id_etat);
            if ($check_etat->libelle == "En panne") {
                $content = "La machine " . $machine->code . " est déclarée en panne par l'utilisateur " . Auth::user()->username . ".";
                $adminId = Role::where("role", "Admin")->first()->id;
                $techId = Role::where("role", "Technicien")->first()->id;
                broadcast(new NewNotificationSent(Notification::create(["title" => "Panne machine", "content" => $content, "to_role" => $adminId])))->toOthers();
                broadcast(new NewNotificationSent(Notification::create(["title" => "Panne machine", "content" => $content, "to_role" => $techId])))->toOthers();
            }
            if ($check_etat->libelle == "En marche" && $current_etat == "En panne") {
                $content = "La machine " . $machine->code . " n'est plus en panne.";
                $adminId = Role::where("role", "Admin")->first()->id;
                $techId = Role::where("role", "Technicien")->first()->id;
                broadcast(new NewNotificationSent(Notification::create(["title" => "Panne machine", "content" => $content, "to_role" => $adminId])))->toOthers();
                broadcast(new NewNotificationSent(Notification::create(["title" => "Panne machine", "content" => $content, "to_role" => $techId])))->toOthers();
            }

            $machine->update($request->all());
            //HistoriqueActivite::create(["activite" => "Modification de machine", "id_machine" => $machine->id, "id_user" => Auth::id()]);

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
