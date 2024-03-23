<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Echange extends Model
{
    use HasFactory;
    protected $fillable = ["id_machine", "id_chaine_from", "id_chaine_to", "date_heure", "isActive"];
    protected $with = ['chaineFrom', 'chaineTo', "machine"];

    function chaineFrom(): BelongsTo
    {
        return $this->belongsTo(Chaine::class, "id_chaine_from");
    }


    function chaineTo(): BelongsTo
    {
        return $this->belongsTo(Chaine::class, "id_chaine_to");
    }

    function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, "id_machine")->without("echanges");
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('date_heure', 'DESC');
        });
    }
}
