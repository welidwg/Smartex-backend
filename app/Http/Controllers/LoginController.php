<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors(), 'type' => 'error'], 400);
        }

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $user = Utilisateur::where("username", $request->username)->with("role")->first();
            $token = $user->createToken('LoginToken')->accessToken;
            return response()->json(['token' => $token, "user" => $user, "type" => "success"], 200);
        } else {
            return response()->json(['message' => 'Nom d\'utilisateur ou mot de passe incorrect', 'type' => "error"], 401);
        }
    }
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Déconnexion réussie', "type" => "success"], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 'type' => "error"], 401);
        }
    }
}
