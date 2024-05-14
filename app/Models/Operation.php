<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operation extends Model
{
    use HasFactory;
    protected $fillable = ["libelle", "id_reference", "id_gamme", "temps"];

    function reference(): BelongsTo
    {
        return $this->belongsTo(Reference::class, "id_reference");
    }


    function gamme(): BelongsTo
    {
        return $this->belongsTo(Gamme::class, "id_gamme");
    }

    // function competences(): HasMany
    // {
    //     return $this->hasMany(Competence::class, "id_operation");
    // }
}
