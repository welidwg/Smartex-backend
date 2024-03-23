<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriqueActivite extends Model
{
    use HasFactory;
    protected $fillable = ["id_machine", "id_user", "activite"];
    protected $with = ["user", "machine"];


    function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, "id_machine");
    }

    function user(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, "id_user")->without("activities");
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('created_at', 'DESC');
        });
    }
}
