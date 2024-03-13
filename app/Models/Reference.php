<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reference extends Model
{
    use HasFactory;
    protected $fillable = ["ref"];

    function machines(): HasMany
    {
        return $this->hasMany(Machine::class, "id_reference");
    }
}
