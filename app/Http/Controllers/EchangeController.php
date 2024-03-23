<?php

namespace App\Http\Controllers;

use App\Events\NewNotificationSent;
use App\Models\Echange;
use App\Models\HistoriqueActivite;
use App\Models\Notification;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Auth;

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
            $adminRole = Role::where("role", "Admin")->first();
            $ex = Echange::create($request->all());
            HistoriqueActivite::create(["activite" => "Echange de machine", "id_machine" => $request->id_machine, "id_user" => Auth::id()]);
            $notif = Notification::create(["title" => "Echange de machine", "content" => "La machine " . $ex->machine->code . " est déplacée vers la chaîne : " . $ex->chaineTo->libelle . " par l'utilisateur " . Auth::user()->username, "to_role" => $adminRole->id]);
            broadcast(new NewNotificationSent($notif))->toOthers();

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
            HistoriqueActivite::create(["activite" => "Annulation d'échange machine", "id_machine" => $echange->id_machine, "id_user" => Auth::id()]);
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
