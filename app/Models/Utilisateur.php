<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Utilisateur extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ["username", "password", "role"];
    protected $with = ["role", "activities"];
    protected $hidden = [
        'password',
    ];
    function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, "role");
    }

    function activities(): HasMany
    {
        return $this->hasMany(HistoriqueActivite::class, "id_user")->without("user");
    }
}
