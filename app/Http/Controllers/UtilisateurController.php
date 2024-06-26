<?php

namespace App\Http\Controllers;

use App\Models\Chaine;
use App\Models\EtatMachine;
use App\Models\HistoriqueActivite;
use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UtilisateurController extends Controller
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
        return json_encode(Utilisateur::where("username", "like", "%$search%")->with("role")->get());
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
        try {
            $validator = Validator::make($request->all(), Utilisateur::$rules, Utilisateur::$messages);
            if ($validator->fails()) {
                return json_encode([
                    'type' => "error",
                    'message' => $validator->errors()
                ]);
            }
            $data = $request->all();
            $data["password"] = Hash::make($data["password"]);
            $new = Utilisateur::create($data);
            HistoriqueActivite::create(["activite" => "Ajout utilisateur " . $new->username, "id_machine" => null, "id_user" => Auth::id()]);
            return response(json_encode(["message" => "Utilisateur bien crée", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }
    public function init()
    {
        //
        try {
            $checkRoles = Role::all();
            $etats = ["En marche", "En panne", "En arrêt"];
            $chaines = ["CH17", "CH18", "CH16"];
            $id_admin = 0;
            if ($checkRoles->count() == 0) {
                $roles = ["Admin", "Technicien","Mécanicien"];

                foreach ($roles as $role) {
                    $new = Role::create(["role" => $role]);
                    if ($role == "Admin") {
                        $id_admin = $new->id;
                    }
                }
            } else {
                $admin = Role::where("role", "Admin")->first();
                $id_admin = $admin->id;
            }
            foreach ($etats as $etat) {
                EtatMachine::create(["libelle" => $etat]);
            }
            foreach ($chaines as $chaine) {
                Chaine::create(["libelle" => $chaine]);
            }

            $data = ["username" => "admin", "password" => "1234", "role" => $id_admin];
            $data["password"] = Hash::make($data["password"]);
            $new = Utilisateur::create($data);
            return response(json_encode(["message" => "Utilisateur bien crée", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Utilisateur  $machine
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return json_encode(Utilisateur::find($id));
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
            $user = Utilisateur::find($id);
            $rules = Utilisateur::$rules;
            $rules['username'] = $rules['username'] . ',username,' . $id;
            $validator = Validator::make($request->all(), $rules, Utilisateur::$messages);
            if ($validator->fails()) {
                return json_encode([
                    'type' => "error",
                    'message' => $validator->errors()
                ]);
            }
            $data = $request->all();
            if ($data["newPass"] != "") {
                if (strlen($data["newPass"]) < 5) {
                    return json_encode([
                        'type' => "error",
                        'message' => ["password" => ["Le mot de passe doit comporter 6 caractères au minimum."]]
                    ]);
                }
                $newPass = Hash::make($data["newPass"]);
            } else {
                $newPass = $user->password;
            }
            $data["password"] = $newPass;
            $user->update($data);
            HistoriqueActivite::create(["activite" => "Modification d'utilisateur " . $user->username, "id_machine" => null, "id_user" => Auth::id()]);

            return response(json_encode(["message" => "Utilisateur modifié", "type" => "success", "user" => $user]), 200);
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
            $user = Utilisateur::find($id);
            HistoriqueActivite::create(["activite" => "Suppression d'utilisateur " . $user->username, "id_machine" => null, "id_user" => Auth::id()]);
            $user->delete();
            return response(json_encode(["message" => "Utilisateur supprimé", "type" => "success"]), 200);
        } catch (\Throwable $th) {
            return response(json_encode(["message" => $th->getMessage(), "type" => "error"]), 500);
        }
    }
}
