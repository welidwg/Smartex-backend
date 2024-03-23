<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriqueMachine extends Model
{
    use HasFactory, HasFactory;

    protected $fillable = ["id_machine", "historique", "date_heure", "added_by"];

    function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, "id_machine")->without("historique");
    }

    function user_added(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, "added_by");
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('date_heure', 'DESC');
        });
    }
}
