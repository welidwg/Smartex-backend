<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Competence extends Model
{
    use HasFactory;

    protected $fillable = ["id_ouvrier", "id_operation", "competence"];

    function operations(): BelongsTo
    {
        return $this->belongsTo(Operation::class, "id_operation");
    }
}
