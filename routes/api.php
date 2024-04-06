<?php

use App\Http\Controllers\ChaineController;
use App\Http\Controllers\EchangeController;
use App\Http\Controllers\EtatMachineController;
use App\Http\Controllers\HistoriqueActiviteController;
use App\Http\Controllers\HistoriqueMachineController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PanneMachineController;
use App\Http\Controllers\ReferencesController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UtilisateurController;
use App\Models\HistoriqueMachine;
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

Route::middleware(["auth:api"])->group(function () {
    Route::resource("/utilisateur", UtilisateurController::class);
    Route::resource("/reference", ReferencesController::class);
    Route::resource("/historique", HistoriqueMachineController::class);
    Route::resource("/machine", MachineController::class);
    Route::resource("/echange", EchangeController::class);
    Route::resource("/chaine", ChaineController::class);
    Route::resource("/etat", EtatMachineController::class);
    Route::resource("/role", RoleController::class);
    Route::resource("/historique", HistoriqueMachineController::class);
    Route::resource("/historiqueActivite", HistoriqueActiviteController::class);
    Route::resource("/pannesMachine", PanneMachineController::class);
    Route::get("/hist/{idmachine}", [HistoriqueMachineController::class, "all"])->name("all");
    Route::get('/estimate/{id_machine}', [HistoriqueMachineController::class, 'getEstimation']);
    Route::get('/notification/getByRole/{id_role}', [NotificationController::class, 'getByRole']);
    Route::resource("/notification", NotificationController::class);
    Route::post("/logout", [LoginController::class, "logout"])->name("logout");
});

Route::post("/login", [LoginController::class, "login"])->name("login");
Route::post("/init", [UtilisateurController::class, "init"])->name("init");
