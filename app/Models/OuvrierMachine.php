<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OuvrierMachine extends Model
{
    use HasFactory;

    protected $fillable = ["id_ouvrier", "id_reference"];

    function ouvrier(): BelongsTo
    {
        return $this->belongsTo(Ouvrier::class, "id_ouvrier");
    }

    function reference(): BelongsTo
    {
        return $this->belongsTo(Reference::class, "id_reference");
    }
}
