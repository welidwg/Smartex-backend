<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ouvrier extends Model
{
    use HasFactory;

    protected $with = ["competences"];

    function competences(): HasMany
    {
        return $this->hasMany(Competence::class, "id_ouvrier");
    }
}
