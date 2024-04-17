<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chaine extends Model
{
    use HasFactory;
    protected $fillable = ["libelle"];
    function ouvriers(): HasMany
    {
        return $this->hasMany(Ouvrier::class, "id_chaine")->with("competences.operations.reference");
    }
}
