<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gamme extends Model
{
    use HasFactory;

    function operations(): HasMany
    {
        return $this->hasMany(Operation::class, "id_gamme");
    }
    
    
}
