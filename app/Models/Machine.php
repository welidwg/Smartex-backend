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
}
