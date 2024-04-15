<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Operation extends Model
{
    use HasFactory;

    function reference(): BelongsTo
    {
        return $this->belongsTo(Reference::class, "id_reference");
    }


    function gamme(): BelongsTo
    {
        return $this->belongsTo(Gamme::class, "id_gamme");
    }
}
