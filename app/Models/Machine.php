<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Machine extends Model
{
    use HasFactory;
    protected $fillable = ["code", "id_etat", "id_reference", "id_chaine", "parc", "added_by", "edited_by"];
    protected $with = ["chaine", "user_added", "user_edited", "reference", "historique", "echanges", "historiqueActivite"];
    static $rules = [
        'code' => "required|unique:machines",
    ];
    static $messages = [
        "code.required" => "Le code machine est requis.",
        "code.unique" => "Ce code machine est déjà existant.",
    ];
    function chaine(): BelongsTo
    {
        return $this->belongsTo(Chaine::class, "id_chaine");
    }

    function user_added(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, "added_by")->without("activities");
    }

    function user_edited(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, "edited_by")->without("activities");
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
        return $this->hasMany(HistoriqueMachine::class, "id_machine")->with("panne");
    }
    function historiqueActivite(): HasMany
    {
        return $this->hasMany(HistoriqueActivite::class, "id_machine")->without("machine");
    }
    function echanges(): HasMany
    {
        return $this->hasMany(Echange::class, "id_machine")->without("machine");
    }
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('code', 'ASC');
        });
    }
}
