<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Laravel\Passport\HasApiTokens;

class Utilisateur extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ["username", "password", "role"];
    protected $with = ["role", "activities"];
    // protected $hidden = [
    //     'password',
    // ];
    static $rules = [
        'username' => "required|unique:utilisateurs",
        'password' => 'required|min:6',
    ];
    static $messages = [
        "username.required" => "Le nom d'utilisateur est requis.",
        "username.min" => "Le nom d'utilisateur doit comporter 6 caractères au minimum.",
        "username.unique" => "Ce nom d'utilisateur est déjà utilisé.",
        "password.required" => "Le mot de passe est requis.",
        "password.min" => "Le mot de passe doit comporter 6 caractères au minimum.",
    ];

    function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, "role");
    }

    function activities(): HasMany
    {
        return $this->hasMany(HistoriqueActivite::class, "id_user")->without("user")->without("machine.historiqueActivite");
    }
}
