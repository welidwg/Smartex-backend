<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Machine extends Model
{
    use HasFactory;
    protected $fillable = ["code", "id_etat", "id_reference", "id_chaine", "parc"];

    function chaine(): BelongsTo
    {
        return $this->belongsTo(Chaine::class, "id_chaine");
    }
    function reference(): BelongsTo
    {
        return $this->belongsTo(Reference::class, "id_reference");
    }
    function etat(): BelongsTo
    {
        return $this->belongsTo(EtatMachine::class, "id_etat");
    }
    function historique(): HasMany
    {
        return $this->hasMany(HistoriqueMachine::class, "id_machine");
    }
    function echanges(): HasMany
    {
        return $this->hasMany(Echange::class, "id_machine");
    }
}
