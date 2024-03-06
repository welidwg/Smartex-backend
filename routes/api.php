<?php

use App\Http\Controllers\ChaineController;
use App\Http\Controllers\EtatMachineController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\ReferencesController;
use App\Http\Controllers\UtilisateurController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource("/utilisateur", UtilisateurController::class);
Route::resource("/reference", ReferencesController::class);
Route::resource("/machine", MachineController::class);
Route::resource("/chaine", ChaineController::class);
Route::resource("/etat", EtatMachineController::class);
