<?php

use App\Http\Controllers\ChaineController;
use App\Http\Controllers\CompetenceController;
use App\Http\Controllers\EchangeController;
use App\Http\Controllers\EtatMachineController;
use App\Http\Controllers\GammeController;
use App\Http\Controllers\HistoriqueActiviteController;
use App\Http\Controllers\HistoriqueMachineController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\OuvrierController;
use App\Http\Controllers\OuvrierMachineController;
use App\Http\Controllers\PanneMachineController;
use App\Http\Controllers\ReferencesController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UtilisateurController;
use App\Models\Gamme;
use App\Models\HistoriqueMachine;
use App\Models\OuvrierMachine;
use App\Models\Role;
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
    Route::resource("/historique", HistoriqueMachineController::class);
    Route::resource("/reference", ReferencesController::class);
    Route::resource("/echange", EchangeController::class);
    Route::resource("/chaine", ChaineController::class);
    Route::resource("/etat", EtatMachineController::class);
    Route::resource("/role", RoleController::class);
    Route::resource("/historique", HistoriqueMachineController::class);
    Route::resource("/historiqueActivite", HistoriqueActiviteController::class);
    Route::resource("/pannesMachine", PanneMachineController::class);
    Route::get('/estimate/{id_machine}', [HistoriqueMachineController::class, 'getEstimation']);
    Route::get('/notification/getByRole/{id_role}', [NotificationController::class, 'getByRole']);
    Route::resource("/notification", NotificationController::class);
    Route::resource("/competence", CompetenceController::class);
    Route::post("/ouvrier/presence", [OuvrierController::class, "markPresence"]);
    Route::post("/ouvrier/presence/auto", [OuvrierController::class, "markPresenceAuto"]);

    Route::post('/gamme/equilibrage', [GammeController::class, 'equilibrage']);
    Route::resource("/gamme", GammeController::class);
    Route::get('/ouvrierMachine/ouvrier/{id}', [OuvrierMachineController::class, 'getOuvrierMachineByOuvrierId']);
    Route::get('/ouvrierMachine/ref/{id}', [OuvrierMachineController::class, 'getOuvrierMachineByRefId']);
    Route::resource("/ouvrierMachine", OuvrierMachineController::class);
    Route::post("/logout", [LoginController::class, "logout"])->name("logout");
    Route::resource("/operation", OperationController::class);
    Route::resource("/ouvrier", OuvrierController::class);
    Route::resource("/machine", MachineController::class);
    Route::post("/predict/{id_machine}", [HistoriqueMachineController::class, "estimateModel"]);

});
Route::post("/login", [LoginController::class, "login"])->name("login");
Route::post("/machine/flask", [MachineController::class, "addFromFlask"]);
Route::get("/hist/{idmachine}", [HistoriqueMachineController::class, "all"])->name("all");
Route::post("/init", [UtilisateurController::class, "init"])->name("init");
Route::post("/allmachines/estimate",[HistoriqueMachineController::class, "estimationAllMachines"]);
